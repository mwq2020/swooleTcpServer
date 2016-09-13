<?php

class HttpServer
{
    public static $instance;
    public $http;
    public $logDir = '/tmp/test.log';
    public function __construct()
    {
        $http = new swoole_http_server("0.0.0.0", 2020);
        $http->set(
            array(
                'worker_num' => 1,
                'daemonize' => true,
                'max_request' => 1,
                'dispatch_mode' => 1
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
        include_once __DIR__.'/Vendor/Bootstrap/Autoloader.php';
        \Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/')->init();

        $header= $request->header;
        ob_start();
        try {
            print_r($header);
            \Test\Models\Common::instance()->dealRequest($request->server);
            $response->status('200');
        } catch (\Exception $e ) {
            $response->status('500');
        }
        $result = ob_get_contents();
        ob_end_clean();
        $response->end($result);
    }

    //开启master主进程【设置进程的名称】
    public function onStart($server)
    {
        file_put_contents($this->logDir,"\r\n onStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name('running master swoole test server.php'); //可以甚至swoole的进程名字 用于区分 {设置主进程的名称}
    }

    //开启task进程【设置进程的名称】
    public function onManagerStart($server)
    {
        file_put_contents($this->logDir,"\r\n onManagerStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name('running manager swoole test server.php'); //可以甚至swoole的进程名字 用于区分{设置主进程的名称}
    }

    //开启worker进程【设置进程的名称】
    public function onWorkerStart($server,$fd)
    {
        file_put_contents($this->logDir,"\r\n onWorkerStart: ".date('Y-m-d H:i:s')." \r\n",FILE_APPEND);
        swoole_set_process_name('running worker swoole test server.php'); //可以甚至swoole的进程名字 用于区分 {设置主进程的名称}
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