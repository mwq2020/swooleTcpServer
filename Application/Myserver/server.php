<?php
namespace Myserver;
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

class WebSocketServer
{
    public static $instance;
    public $serverObj = null;
    public $logDir = '/tmp/swoole.log';
    public $applicationRoot = __DIR__;

    public function __construct() 
    {
        //file_put_contents($this->logDir,"\r\n WebSocketServerRoot: ".date('Y-m-d H:i:s').var_export($this->applicationRoot,true)."\r\n",FILE_APPEND);

        $server = new \swoole_server("0.0.0.0", 7000);
        $server->set(
            array(
                'worker_num'    => 4,   //工作进程数量
                'max_request'   => 8, //多少次调用后再重启新的进程
                'daemonize' => true
            )
        );

        $server->on('start',array($this,'onStart'));
        $server->on('managerStart',array($this,'onManagerStart'));
        $server->on('workerStart',array($this,'onWorkerStart'));
        $server->on('connect',array($this,'onConnect'));
        $server->on('receive',array($this,'onReceive'));
        $server->on('close',array($this,'onClose'));
        //$server->on('Shutdown',array($this,'onShutdown'));
        $server->on('workerError',array($this,'onWorkerError'));
        $this->serverObj = $server;
        $server->start();

    }

    public function onReceive($server,$fd,$from_id,$data)
    {
        register_shutdown_function(array($this,'handleFatalError'),$server,$fd);

        file_put_contents($this->logDir,"\r\n onReceive: ".date('Y-m-d H:i:s').var_export($data,true)."\r\n",FILE_APPEND);
        if(trim($data) == 'stats'){
            $stats = $this->getServiceStat();
            return $server->send($fd,$stats);
        } elseif(trim($data) == 'help') {
            return $server->send($fd,"youcan use  stats|help|quit \r\n");
            //$server->close($fd);
        } elseif(trim($data) == 'quit') {
            $server->close($fd);
        }
        $data = $this->dealRequest($data);
        $server->send($fd, json_encode($data));
        $server->close($fd);
    }

    public function dealRequest($data)
    {
        $data = json_decode($data,true);
        $class = $data['class'];
        $method = $data['method'];
        $param_array = $data['param_array'];

        \Statistics\StatisticClient::tick($class,$method);

        $success = true;
        $code = 200;
        $msg = '';
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
        } catch(\Exception $e) {
            $success = false;
            // 有异常
            // 发送数据给客户端，发生异常，调用失败
            $code = $e->getCode() == 200 ? 2001 : ($e->getCode() ? $e->getCode() : 500);
            $ret_data = array('code'=>$code,'flag'=>false, 'msg'=>$e->getMessage(), 'data'=>$e);
        }
        try{
            \Statistics\StatisticClient::report($class,$method,$success,$code,$msg);
        } catch(\Exception $e) {
            file_put_contents($this->logDir,"\r\n ".$e." \r\n",FILE_APPEND);
        }

        return $ret_data;
    }

    //开启链接时回调
    public function onConnect($server,$fd)
    {
        file_put_contents($this->logDir,"\r\n onConnect: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
    }

    //关闭链接时回调
    public function onClose($server,$fd)
    {
        file_put_contents($this->logDir,"\r\n onClose: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
    }

    //开启task进程【设置进程的名称】
    public function onWorkerError($server,$fd,$worker_pid,$exit_code)
    {
        file_put_contents($this->logDir,"\r\n onWorkerStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
    }

    //开启master主进程【设置进程的名称】
    public function onStart($server)
    {
        file_put_contents($this->logDir,"\r\n onStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name('running master swoole bestdo server.php'); //可以甚至swoole的进程名字 用于区分 {设置主进程的名称}
    }

    //开启task进程【设置进程的名称】
    public function onManagerStart($server)
    {
        file_put_contents($this->logDir,"\r\n onManagerStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name('running manager swoole bestdo server.php'); //可以甚至swoole的进程名字 用于区分{设置主进程的名称}
    }

    //开启worker进程【设置进程的名称】
    public function onWorkerStart($server,$fd)
    {
        include_once $this->applicationRoot.'/../../Vendor/Bootstrap/Autoloader.php';
        \Bootstrap\Autoloader::instance()->addRoot($this->applicationRoot.'/')->addRoot($this->applicationRoot.'/../../Vendor/')->init();
        file_put_contents($this->logDir,"\r\n onWorkerStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name('running worker swoole bestdo  server.php'); //可以甚至swoole的进程名字 用于区分 {设置主进程的名称}
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
                    file_put_contents($this->logDir,"\r\n handleFatalError: ".date('Y-m-d H:i:s')." \r\n".$log."\r\n",FILE_APPEND);

                    $data = array('code'=>500, 'flag'=>false, 'msg'=>$log, 'data'=>'');
                    $server->send($fd, json_encode($data));
                    $server->close($fd);
                    //break;
                default:
                    break;
            }
        }
        file_put_contents($this->logDir,"\r\n register_shutdown_function: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
    }

    /*

    //开启task进程【设置进程的名称】
    public function onShutdown($server,$fd)
    {
        $errors = error_get_last();
        file_put_contents($this->logDir,"\r\n onWorkerStart: ".date('Y-m-d H:i:s')."---".print_r($errors,true)." \r\n",FILE_APPEND);
    }

    //进程结束的时候调用
    public function onFinish()
    {

    }

    //master和worker都有此回调定时处理器
    public function onTimer()
    {

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

    //开启单例模式
    public static function getInstance() 
    {
        if (!self::$instance) {
            self::$instance = new WebSocketServer;
        }
        return self::$instance;
    }

}

WebSocketServer::getInstance();


//   $serv->reload() 可以用内置的方法刷新进程，达到热更新

