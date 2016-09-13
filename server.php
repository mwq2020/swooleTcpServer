<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);


class WebSocketServer
{
    public static $instance;
    public $logDir = '/tmp/swoole.log';
    public function __construct() 
    {
        $server = new swoole_server("0.0.0.0", 7000);
        $server->set(
            array(
                'worker_num'    => 2,   //工作进程数量
                'max_request'   => 10, //多少次调用后再重启新的进程
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
        $server->start();
    }

    public function onReceive($server,$fd,$from_id,$data)
    {
        register_shutdown_function(array($this,'handleFatalError'),$server,$fd);

        file_put_contents($this->logDir,"\r\n onReceive: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
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

        try
        {
            include_once __DIR__.'/Vendor/Bootstrap/Autoloader.php';
            \Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/')->init();

            $class_name = "\\Handler\\{$class}";
            //判断类存在与否
            if(!class_exists($class_name))
            {
                throw new \Exception('类【'.$class.'】不存在' ,'500');
            }

            //判断方法存在与否
            $obj_class = new $class_name;
            if(!method_exists($obj_class,$method)){
                throw new \Exception('类【'.$class.'】不包含方法【'.$method.'】' ,'500');
            }
            $ret = call_user_func_array(array($obj_class, $method), $param_array);
            // 发送数据给客户端，调用成功，data下标对应的元素即为调用结果
            $ret_data = array('code'=>200, 'flag'=>true, 'msg'=>'ok', 'data'=>$ret);
        }
            // 有异常
        catch(\Exception $e)
        {
            // 发送数据给客户端，发生异常，调用失败
            $code = $e->getCode() == 200 ? 2001 : ($e->getCode() ? $e->getCode() : 500);
            $ret_data = array('code'=>$code,'flag'=>false, 'msg'=>$e->getMessage(), 'data'=>$e);
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
        include_once __DIR__.'/Vendor/Bootstrap/Autoloader.php';
        \Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/')->init();
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

