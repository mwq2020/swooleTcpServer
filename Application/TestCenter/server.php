<?php
namespace TestCenter;
ini_set("display_errors", "On");
error_reporting(E_ALL ^ E_NOTICE);

class HttpServer
{
    public static $instance;
    public $http;
    public $logDir = '/tmp/test.log';
    public $applicationRoot = __DIR__;

    public $serverNamePrefix = 'swooleServer[php] ';//swoole服务的进程名称前缀
    public $serverName = 'TestClient';//自己的服务名称
    public $serverHost = '0.0.0.0';//服务的绑定ip
    public $serverPort = '2020';//服务的绑定端口

    public function __construct()
    {
        $http = new \swoole_http_server($this->serverHost, $this->serverPort);
        $http->set(
            array(
                'worker_num' => 1,
                'daemonize' => true,
                'max_request' => 1,
                //'dispatch_mode' => 1
            )
        );
        $http->on('Request',array($this , 'onRequest'));
        $http->on('start',array($this,'onStart'));
        $http->on('managerStart',array($this,'onManagerStart'));
        $http->on('workerStart',array($this,'onWorkerStart'));
        $http->start();
    }

    /**
     * 链接进来时的处理
     * @param $request
     * @param $response
     */
    public function onRequest($request,$response)
    {
        register_shutdown_function(array($this,'handleFatalError'),$request,$response);
        try {
            (new \FrameWork\Controller)->dealRequest($request,$response);
        } catch (\Exception $e ) {
            $result = $e->getMessage();
            $response->status('500');
            $response->header("Content-Type",'text/html');
            $response->header('Connection','keep-alive');
            $response->header('Content-Length',strlen($result));
            $response->end($result);
        }
    }

    //开启master主进程【设置进程的名称】
    public function onStart($server)
    {
        file_put_contents($this->logDir,"\r\n onStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' master listen['.$this->serverHost.':'.$this->serverPort.']'); //可以甚至swoole的进程名字 用于区分 {设置主进程的名称}
    }

    //开启task进程【设置进程的名称】
    public function onManagerStart($server)
    {
        file_put_contents($this->logDir,"\r\n onManagerStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' manager listen['.$this->serverHost.':'.$this->serverPort.']'); //可以甚至swoole的进程名字 用于区分{设置主进程的名称}
    }

    //开启worker进程【设置进程的名称】
    public function onWorkerStart($server,$fd)
    {
        include_once $this->applicationRoot.'/../../Vendor/Bootstrap/Autoloader.php';
        \Bootstrap\Autoloader::instance()->addRoot($this->applicationRoot.'/')->addRoot($this->applicationRoot.'/../../Vendor/')->init();

        file_put_contents($this->logDir,"\r\n onWorkerStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' worker listen['.$this->serverHost.':'.$this->serverPort.']'); //可以甚至swoole的进程名字 用于区分 {设置主进程的名称}
    }

    //当tcpworker进程处理崩溃的时候出发
    public function handleFatalError($request,$response)
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
                    $response->status(500);
                    $response->end($log);
                break;
                default:
                    break;
            }
        }
        file_put_contents($this->logDir,"\r\n register_shutdown_function: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new HttpServer;
        }
        return self::$instance;
    }
}

HttpServer::getInstance();