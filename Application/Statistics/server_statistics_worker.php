<?php

/**
 * 收集并且处理统计相关数据
 * Class StatisticsWorker
 */


class StatisticsWorker
{
    public $logDir = '/tmp/swoole.log';
    public static $instance;
    private $mongo; //mongo链接connect
    protected $handleWorkerPort = 55656;

    public $serverNamePrefix = 'swooleServer[php] ';//swoole服务的进程名称前缀
    public $serverName = 'statistics_service';//自己的服务名称
    public $serverHost;//服务的绑定ip
    public $serverPort;//服务的绑定端口

    public $swooleTable = null;

    public function __construct()
    {

    }

    /**
     * 启动swoole_server 入口
     * @param string $ip
     * @param int $port
     * @param $mode
     * @param $type
     */
    public function run($ip="0.0.0.0", $port= 55656, $mode = SWOOLE_PROCESS, $type=SWOOLE_SOCK_TCP)
    {
        $this->serverHost = $ip;
        $this->serverPort = $port;
        if (empty($port)) {
            $port = $this->handleWorkerPort;
        } else {
            $this->handleWorkerPort = $port;
        }

        //sucess_count  fail_count  success_cost_time  fail_cost_time
        $swooleTable = new \swoole_table(24576); //最大存储1024行 指定的时候只能指定2的指数列
        $swooleTable->column('sucess_count', swoole_table::TYPE_INT, 8);     //成功调用次数
        $swooleTable->column('fail_count', swoole_table::TYPE_INT, 8);       //失败调用次数
        $swooleTable->column('success_cost_time', swoole_table::TYPE_FLOAT); //成功耗费的总时间
        $swooleTable->column('fail_cost_time', swoole_table::TYPE_FLOAT);    //失败耗费的总时间
        $swooleTable->create();

        //sucess_count  fail_count  success_cost_time  fail_cost_time
        $allTable = new \swoole_table(128); //最大存储1024行 指定的时候只能指定2的指数列
        $allTable->column('sucess_count', swoole_table::TYPE_INT, 8);     //成功调用次数
        $allTable->column('fail_count', swoole_table::TYPE_INT, 8);       //失败调用次数
        $allTable->column('success_cost_time', swoole_table::TYPE_FLOAT); //成功耗费的总时间
        $allTable->column('fail_cost_time', swoole_table::TYPE_FLOAT);    //失败耗费的总时间
        $allTable->create();

        $serv = new \swoole_server($ip, $port, $mode, $type);	//处理客户端发送的数据 55656
        //$serv->addlistener('0.0.0.0', $this->handleProviderPort, SWOOLE_SOCK_TCP|SWOOLE_SOCK_UDP); //处理统计页面请求的数据 55858
        //$serv->addlistener('0.0.0.0', $this->udpFinderport, SWOOLE_SOCK_UDP); //recv udp broadcast 55859
        $serv->swooleTable = $swooleTable; //统计技术表(单个接口统计)
        $serv->allTable = $allTable; //统计技术表（全局）
        $serv->set(array(
                'worker_num'    => 2,   //工作进程数量
                'max_request'   => 8, //多少次调用后再重启新的进程
                'daemonize' => true,
                //'log_file' => $this->logDir,
            ));
        $serv->on('Start', array($this, 'onStart'));
        $serv->on('ManagerStart', array($this, 'onManagerStart'));
        $serv->on('WorkerStart', array($this, 'onWorkerStart'));

        $serv->on('Connect', array($this, 'onConnect'));
        $serv->on('Receive', array($this, 'onReceive'));
        $serv->on('Task', array($this, 'onTask'));

        $serv->on('Finish', array($this, 'onFinish'));
        $serv->on('WorkerError', array($this, 'onWorkerError'));
        $serv->on('Close', array($this, 'onClose'));
        $serv->on('WorkerStop', array($this, 'onWorkerStop'));
        $serv->on('Shutdown', array($this, 'onShutdown'));
        $serv->start();
    }

    /**
     * swoole_start 回调函数
     */
    public function onStart(\swoole_server $serv)
    {
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' master listen['.$this->serverHost.':'.$this->serverPort.']');
    }

    /**
     * manage进程启动时回调
     */
    public function onManagerStart($server)
    {
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' manager listen['.$this->serverHost.':'.$this->serverPort.']');
    }
    /**
     * 进程启动
     * @param unknown $serv
     * @param unknown $worker_id
     */
    public function onWorkerStart($serv, $worker_id)
    {
        include_once __DIR__.'/../../Vendor/Bootstrap/Autoloader.php';
        \Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/')->addRoot(__DIR__.'/../../Vendor/')->init();

        $worker_num = isset($serv->setting['worker_num']) ? $serv->setting['worker_num'] : 1;
        if ($worker_id >= $worker_num) {
            swoole_set_process_name($this->serverNamePrefix.$this->serverName.' task listen['.$this->serverHost.':'.$this->serverPort.']');
        } else {
            swoole_set_process_name($this->serverNamePrefix.$this->serverName.' worker listen['.$this->serverHost.':'.$this->serverPort.']');
            // 定时保存统计数据
            if ($worker_id==0) {
                $serv->tick(59000, function() use ($serv, $worker_id) {
                    $this->saveStatisticsData($serv);
                });
            }
        }
    }

    /**
     * 日志保存在mongodb中
     */
    public function getMongo()
    {
        if (empty($this->mongo) || !$this->mongo->getHosts()) {
            $config =\Config\Mongo::getConfig();
            //$this->log(json_encode($config));
            $this->mongo = new \MongoClient('mongodb://'.$config['host'].':'.$config['port']);
        }
        return $this->mongo;
    }

    //添加日志到文件
    public function log($content,$dir='')
    {
        if(empty($dir)){
            $dir = $this->logDir;
        }

        if(is_array($content) || is_object($content)){
            $content = '['.date('Y-m-d H:i:s').']'.PHP_EOL;
            file_put_contents($dir, print_r($content,true), FILE_APPEND);
        } else {
            $content = '['.date('Y-m-d H:i:s').']'.$content.PHP_EOL;
            file_put_contents($dir, $content, FILE_APPEND);
        }
    }

    //解包
    public static function decode($buffer)
    {
        $length = unpack('N', $buffer)[1];
        $string = substr($buffer, -$length);
        $data = json_decode($string, true);
        return $data;
    }


    /**
     * 保存统计数据到db中
     */
    public function saveStatisticsData($server)
    {
        $this->log('saveStatisticsData in');

        //单个接口流量、耗时统计记录
        $overdueKeys = array();
        $conn = $this->getMongo();
        foreach($server->swooleTable as $key => $row){
            $timestamp = substr($key,-10);
            if((time() - $timestamp) >= 90) {
                $temp = explode('|',$key);
                $data = array(
                    'project_name'  => $temp[0],
                    'class_name'    => $temp[1],
                    'function_name' => $temp[2],
                    'request_ip'    => $temp[3],
                    'local_server_ip'     => current(swoole_get_local_ip()),
                    'timestamp'   => $timestamp,
                    'time_minute'   => date('Y-m-d H:i:s',$timestamp),
                    'success_count' => $row['sucess_count'],
                    'fail_count'    => $row['fail_count'],
                    'success_cost_time' => $row['success_cost_time'],
                    'fail_cost_time'    => $row['fail_cost_time'],
                );
                $db = $conn->selectDB('Statistics');
                $collection = $db->selectCollection($data['project_name']);

                $collection->insert($data);
                array_push($overdueKeys,$key);
            } else {
                $this->log('onTimer api ['.$key.'<-->'.date('Y-m-d H:i:s',$timestamp).'] is not time to save');
            }
        }
        foreach($overdueKeys as $keyNum => $key){
            $server->swooleTable->del($key);
            unset($overdueKeys[$keyNum]);
        }

        //全局流量、耗时统计记录
        foreach($server->allTable as $key => $row){
            if((time() - $key) >= 90) {
                $data = array(
                    'local_server_ip'     => current(swoole_get_local_ip()),
                    'timestamp'   => $key,
                    'time_minute'   => date('Y-m-d H:i:s',$key),
                    'success_count' => $row['sucess_count'],
                    'fail_count'    => $row['fail_count'],
                    'success_cost_time' => $row['success_cost_time'],
                    'fail_cost_time'    => $row['fail_cost_time'],
                );

                $db = $conn->selectDB('Statistics');
                $collection = $db->selectCollection('All_Statistics');
                $collection->insert($data);
                array_push($overdueKeys,$key);
            } else {
                $this->log('onTimer allplatform ['.$key.'<-->'.date('Y-m-d H:i:s',$key).'] is not time to save');
            }
        }
        foreach($overdueKeys as $keyNum => $key){
            $server->allTable->del($key);
            unset($overdueKeys[$keyNum]);
        }
    }

    /**
     * 建立链接
     * @param \swoole_server $serv
     * @param unknown $fd
     * @param unknown $from_id
     */
    public function onConnect(\swoole_server $serv, $fd, $from_id)
    {
        //echo "Worker#{$serv->worker_pid} Client[$fd@$from_id]: Connect.\n";
    }

    /**
     * 接收数据
     * @param \swoole_server $serv
     * @param unknown $fd
     * @param unknown $from_id
     * @param unknown $data
     */
    public function onReceive(\swoole_server $serv, $fd, $from_id, $data)
    {
        $data = self::decode($data);
        $connInfo = $serv->connection_info($fd, $from_id);
        if ($connInfo['server_port'] == $this->handleWorkerPort) {
            $projectName    = $data['project_name']; //项目名称
            $className      = $data['class_name'];     //调用类名
            $functionName   = $data['function_name']; //调用函数名
            $cost_time      = $data['cost_time'];//耗费时间
            $success        = $data['is_success']; //是否成功
            $code           = $data['code']; //服务code
            $msg            = $data['msg']; //日志消息
            $ip = $serv->connection_info($fd)['remote_ip'];//当前链接进来的ip

            //单一接口流量、耗时统计记录
            $statistics_key = $projectName.'|'.$className.'|'.$functionName.'|'.$ip.'|'.(strtotime(date('Y-m-d H:i:00'))+60);
            if(!$serv->swooleTable->exist($statistics_key)){
                if($success){
                    $serv->swooleTable->set($statistics_key,array('sucess_count'=>1,'fail_count'=>0,'success_cost_time'=>$cost_time,'fail_cost_time'=>0));
                } else {
                    $serv->swooleTable->set($statistics_key,array('sucess_count'=>0,'fail_count'=>1,'success_cost_time'=>0,'fail_cost_time'=>$cost_time));
                }
            } else {
                if($success){
                    $serv->swooleTable->incr($statistics_key,'sucess_count',1);
                    $serv->swooleTable->incr($statistics_key,'success_cost_time',$cost_time);
                } else {
                    $serv->swooleTable->incr($statistics_key,'fail_count',1);
                    $serv->swooleTable->incr($statistics_key,'fail_cost_time',$cost_time);
                }
            }

            //全局流量、耗时统计记录
            $all_statistics_key = strtotime(date('Y-m-d H:i:00'))+60;
            if(!$serv->allTable->exist($all_statistics_key)){
                if($success){
                    $serv->allTable->set($all_statistics_key,array('sucess_count'=>1,'fail_count'=>0,'success_cost_time'=>$cost_time,'fail_cost_time'=>0));
                } else {
                    $serv->allTable->set($all_statistics_key,array('sucess_count'=>0,'fail_count'=>1,'success_cost_time'=>0,'fail_cost_time'=>$cost_time));
                }
            } else {
                if($success){
                    $serv->allTable->incr($all_statistics_key,'sucess_count',1);
                    $serv->allTable->incr($all_statistics_key,'success_cost_time',$cost_time);
                } else {
                    $serv->allTable->incr($all_statistics_key,'fail_count',1);
                    $serv->allTable->incr($all_statistics_key,'fail_cost_time',$cost_time);
                }
            }

            //日志记录在mongodb
            $mongoHander = $this->getMongo();
            if(!empty($mongoHander)) {
                $data['remote_ip'] = $ip;
                $data['msg'] = $msg;
                $monogoDb = $mongoHander->selectDB('StatisticsLog');
                $mongoCollection = $monogoDb->selectCollection($projectName);
                $mongoCollection->insert($data);
            }
        }
    }

    /**
     * task任务
     * @param \swoole_server $serv
     * @param unknown $task_id
     * @param unknown $from_id
     * @param unknown $data
     * @return void|multitype:string
     */
    public function onTask(\swoole_server $serv, $task_id, $from_id, $data)
    {
        //保留回调函数,暂时不用
    }

    /**
     * task执行完毕调用
     * @param \swoole_server $serv
     * @param unknown $task_id
     * @param unknown $data
     */
    public function onFinish(\swoole_server $serv, $task_id, $data)
    {
        //保留回调函数,暂时不用
    }

    /**
     * worker出现问题调用
     * @param \swoole_server $serv
     * @param unknown $worker_id
     * @param unknown $worker_pid
     * @param unknown $exit_code
     */
    public function onWorkerError(\swoole_server $serv, $worker_id, $worker_pid, $exit_code)
    {
        echo "worker abnormal exit. WorkerId=$worker_id|Pid=$worker_pid|ExitCode=$exit_code\n";
    }

    /**
     * 链接断开
     * @param unknown $serv
     * @param unknown $fd
     * @param unknown $from_id
     */
    public function onClose($serv, $fd, $from_id)
    {
        //$this->log("Worker#{$serv->worker_pid} Client[$fd@$from_id]: fd=$fd is closed");
        //$redis = $this->getRedis();
        //$redis->delete('key'.$fd);
    }

    /**
     * 关闭进程
     * @param unknown $serv
     * @param unknown $worker_id
     */
    public function onWorkerStop($serv, $worker_id)
    {
        //echo "WorkerStop[$worker_id]|pid=" . $serv->worker_pid . ".\n";
    }

    /**
     * 关闭服务器
     * @param unknown $serv
     */
    public function onShutdown($serv)
    {
        //echo "Server: onShutdown\n";
    }

}

(new StatisticsWorker)->run('0.0.0.0',55656);