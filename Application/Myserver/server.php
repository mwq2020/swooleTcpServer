<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

class WebSocketServer
{
    public static $instance;
    public $serverObj = null;
    public $logDir = '/tmp/swoole.log';
    public $applicationRoot = __DIR__;

    public $start_timestamp = 0;
    public $end_timestamp = 0;

    public $serverNamePrefix = 'swooleServer[php] ';//swoole服务的进程名称前缀
    public $serverName = 'MyServer';//自己的服务名称
    public $serverHost = '0.0.0.0';//服务的绑定ip
    public $serverPort = '7000';//服务的绑定端口

    public $swooleTable = null;
    public $swooleAtomic = null; //接口访问总的次数

    public $hasStartTimer = false;

    public function __construct() 
    {
        //sucess_count  fail_count  success_cost_time  fail_cost_time
        $swooleTable = new \swoole_table(1024); //最大存储1024行 指定的时候只能指定2的指数列
        $swooleTable->column('sucess_count', swoole_table::TYPE_INT, 8);     //成功调用次数
        $swooleTable->column('fail_count', swoole_table::TYPE_INT, 8);       //失败调用次数
        $swooleTable->column('success_cost_time', swoole_table::TYPE_FLOAT); //成功耗费的总时间
        $swooleTable->column('fail_cost_time', swoole_table::TYPE_FLOAT);    //失败耗费的总时间
        $swooleTable->create();

        //原子技术
        $atomic = new \swoole_atomic(0);

        $server = new swoole_server($this->serverHost, $this->serverPort);
        $server->set(
            array(
                'worker_num'    => 4,   //工作进程数量
                'max_request'   => 8, //多少次调用后再重启新的进程
                'daemonize' => true,
                'log_file' => $this->logDir,
            )
        );

        $server->swooleTable = $swooleTable;
        $server->usedCount = $atomic;

        $server->on('start',array($this,'onStart'));
        $server->on('managerStart',array($this,'onManagerStart'));
        $server->on('workerStart',array($this,'onWorkerStart'));
        //$server->on('Timer',array($this,'onTimer'));
        $server->on('connect',array($this,'onConnect'));
        $server->on('receive',array($this,'onReceive'));
        $server->on('close',array($this,'onClose'));
        //$server->on('Shutdown',array($this,'onShutdown'));
        $server->on('workerError',array($this,'onWorkerError'));
        $this->serverObj = $server;
        $server->start();

        swoole_server_addtimer($server,40);

    }

    public function onReceive($server,$fd,$from_id,$data)
    {
        $server->usedCount->add(1);//接口调用计数
        $this->start_timestamp = microtime(true);
        register_shutdown_function(array($this,'handleFatalError'),$server,$fd);

        //$this->log('onReceive');
        if(trim($data) == 'stats'){
            $stats = $this->getServiceStat();
            return $server->send($fd,$stats);
        } elseif(trim($data) == 'help') {
            return $server->send($fd,"youcan use  stats|help|quit \r\n");
        } elseif(trim($data) == 'quit') {
            $server->close($fd);
        }
        $data = $this->dealRequest($server,$fd,$from_id,$data);
        $server->send($fd, json_encode($data));
        $server->close($fd);
    }

    public function dealRequest($server,$fd,$from_id,$data)
    {
        $data       = json_decode($data,true);
        $class      = $data['class'];
        $method     = $data['method'];
        $param_array = $data['param_array'];
        $ip = $server->connection_info($fd)['remote_ip'];

        //sucess_count  fail_count  success_cost_time  fail_cost_time
        $statistics_key = $this->serverName.'_'.$class.'_'.$method.'_'.date('YmdHi');
        if(!$server->swooleTable->exist($statistics_key)){
            $server->swooleTable->set($statistics_key,array('sucess_count'=>0,'fail_count'=>0,'success_cost_time'=>0,'fail_cost_time'=>0));
        }

        $success = true;//接口的成功或者失败的标志
        $code = 200;    //服务状态code
        $msg = '';      //错误堆栈信息
        try
        {
            $class_name = "\\Handler\\{$class}";
            //判断类存在与否
            if(!class_exists($class_name)){
                throw new \Exception('类【'.$class.'】不存在' ,'500');
            }

            //判断方法存在与否
            $obj_class = new $class_name;
            if(!method_exists($obj_class,$method)){
                throw new \Exception('类【'.$class.'】不包含方法【'.$method.'】' ,'500');
            }
            $ret = call_user_func_array(array($obj_class, $method), $param_array);

            // 发送数据给客户端，调用成功，data下标对应的元素即为调用结果
            $ret_data = array('code'=>$code, 'flag'=>true, 'msg'=>'ok', 'data'=>$ret);
            $server->swooleTable->incr($statistics_key,'sucess_count',1);
        } catch(\Exception $e) {
            $success = false;
            // 有异常 发送数据给客户端，发生异常，调用失败
            $code = $e->getCode() == 200 ? 2001 : ($e->getCode() ? $e->getCode() : 500);
            $msg = ''.$e;
            $ret_data = array('code'=>$code,'flag'=>false, 'msg'=>$e->getMessage(), 'data'=>$e);
            $server->swooleTable->incr($statistics_key,'fail_count',1);
        }
        $this->end_timestamp = microtime(true);
        try{
            //\Statistics\StatisticClient::report($class,$method,$success,$code,$msg);
        } catch(\Exception $e) {
            $this->log(' '.$e);
        }

        return $ret_data;
    }

    //开启链接时回调
    public function onConnect($server,$fd)
    {
        $this->log('onConnect');
    }

    //关闭链接时回调
    public function onClose($server,$fd)
    {
        $this->log('onClose');
    }

    //开启task进程【设置进程的名称】
    public function onWorkerError($server,$fd,$worker_pid,$exit_code)
    {
        $this->log('onWorkerError');
    }

    //开启master主进程【设置进程的名称】
    public function onStart($server)
    {
        $this->log('onStart');
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' master listen['.$this->serverHost.':'.$this->serverPort.']'); //可以甚至swoole的进程名字 用于区分 {设置主进程的名称}
    }

    //开启task进程【设置进程的名称】
    public function onManagerStart($server)
    {
        //$this->log('onManagerStart');
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' manager listen['.$this->serverHost.':'.$this->serverPort.']'); //可以甚至swoole的进程名字 用于区分{设置主进程的名称}
    }

    //开启worker进程【设置进程的名称】
    public function onWorkerStart($server,$worker_id)
    {
        if($worker_id == 0){
            $server->tick(59000, function() use ($server, $worker_id) {
                foreach($server->swooleTable as $key => $row){
                    $this->log('onTimer '.$key.'-'.json_encode($row));
                }
            });
        }

        include_once $this->applicationRoot.'/../../Vendor/Bootstrap/Autoloader.php';
        \Bootstrap\Autoloader::instance()->addRoot($this->applicationRoot.'/')->addRoot($this->applicationRoot.'/../../Vendor/')->init();
        //$this->log('onWorkerStart='.$worker_id);
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' worker listen['.$this->serverHost.':'.$this->serverPort.']'); //可以甚至swoole的进程名字 用于区分 {设置主进程的名称}
    }

    //当tcpworker进程处理崩溃的时候出发
    public function handleFatalError($server,$fd)
    {
        $error = error_get_last();
        if (isset($error['type']))
        {
            switch ($error['type'])
            {
                case E_ERROR :
                case E_PARSE :
                case E_CORE_ERROR :
                case E_COMPILE_ERROR :
                    $message = $error['message'];
                    $file = $error['file'];
                    $line = $error['line'];
                    $log = "$message ($file:$line)\nStack trace:\n";
                    $trace = debug_backtrace();
                    foreach ($trace as $i => $t){
                        if (!isset($t['file'])){
                            $t['file'] = 'unknown';
                        }
                        if (!isset($t['line'])){
                            $t['line'] = 0;
                        }
                        if (!isset($t['function'])){
                            $t['function'] = 'unknown';
                        }
                        $log .= "#$i {$t['file']}({$t['line']}): ";
                        if (isset($t['object']) and is_object($t['object'])){
                            $log .= get_class($t['object']) . '->';
                        }
                        $log .= "{$t['function']}()\n";
                    }
                    if (isset($_SERVER['REQUEST_URI'])){
                        $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
                    }
                    $this->log('handleFatalError '.$log);

                    $data = array('code'=>500, 'flag'=>false, 'msg'=>$log, 'data'=>'');
                    $server->send($fd, json_encode($data));
                    $server->close($fd);
                    //break;
                default:
                    break;
            }
        }
        $this->log('register_shutdown_function');
    }

    /*

    //开启task进程【设置进程的名称】
    public function onShutdown($server,$fd)
    {
        $this->log('onShutdown '.$log);
    }


    //主进程结束时
    public function onMasterClose()
    {

    }
    */

    public function getServiceStat()
    {
        $statusStr = '';
        if($this->serverObj){
            $stats = $this->serverObj->stats();
            if($stats){
                $statusStr .= '服务开启时间:'.date("Y-m-d H:i:s",$stats['start_time'])."\r\n";
                $statusStr .= '当前链接数 :'.$stats['connection_num']."\r\n";
                $statusStr .= '接受链接总数  :'.$stats['accept_count']."\r\n";
                $statusStr .= '已关闭链接总数:'.$stats['close_count']."\r\n";
                $statusStr .= '排队任务数量:'.$stats['tasking_num']."\r\n";
            }
        } else {
            $statusStr = "service is not running? \r\n";
        }
        return $statusStr;
    }

    //worker进程可以调用此回调函数
    public function onTimer($server,$interval)
    {
        //$this->log('onTimer count:'.$interval);
    }

    //task进程可以调用此回调函数
    public function onTask(swoole_server $serv,  $task_id, $from_id, $data)
    {
        //$this->log('onTask');
    }

    //task进程可以调用此回调函数
    public function onFinish($serv, $task_id, $data)
    {
        //$this->log('onFinish');
    }

    //开启单例模式
    public static function getInstance() 
    {
        if (!self::$instance) {
            self::$instance = new WebSocketServer;
        }
        return self::$instance;
    }

    public function log($content,$dir='')
    {
        if(empty($dir)){
            $dir = $this->logDir;
        }
        $content = '['.date('Y-m-d H:i:s').']'.$content."\r\n";
        file_put_contents($dir, $content, FILE_APPEND);
    }

}

WebSocketServer::getInstance();


//   $serv->reload() 可以用内置的方法刷新进程，达到热更新

