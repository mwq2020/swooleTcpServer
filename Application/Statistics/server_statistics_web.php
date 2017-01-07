<?php

/**
 * 统计web界面入口
 * Class StatisticsWebServer
 */

class StatisticsWebServer
{
    public static $instance;

    public $serverNamePrefix = 'swooleServer[php] ';//swoole服务的进程名称前缀
    public $serverName = 'statistics_web';//自己的服务名称
    public $serverHost;//服务的绑定ip
    public $serverPort;//服务的绑定端口

    /**
     * 初始化
     */
    public function __construct()
    {
        //register_shutdown_function(array($this, 'handleFatal'));
    }

    /**
     * server start的时候调用 设置主进程名称
     * @param unknown $serv
     */
    public function onStart($serv)
    {
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' master listen['.$this->serverHost.':'.$this->serverPort.']');
    }
    /**
     * worker start时调用
     * @param unknown $serv
     * @param int $worker_id
     */
    public function onWorkerStart($serv, $worker_id)
    {
        $worker_num = isset($serv->setting['worker_num']) ? $serv->setting['worker_num'] : 1;
        $task_worker_num = isset($serv->setting['task_worker_num']) ? $serv->setting['task_worker_num'] : 0;

        if($worker_id >= $worker_num) {
            swoole_set_process_name($this->serverNamePrefix.$this->serverName.' task listen['.$this->serverHost.':'.$this->serverPort.']');
        } else {
            swoole_set_process_name($this->serverNamePrefix.$this->serverName.' worker listen['.$this->serverHost.':'.$this->serverPort.']');
        }

        include_once __DIR__.'/../../Vendor/Bootstrap/Autoloader.php';
        \Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/')->addRoot(__DIR__.'/../../Vendor/')->init();
    }

    public function onManagerStart()
    {
        swoole_set_process_name($this->serverNamePrefix.$this->serverName.' manager listen['.$this->serverHost.':'.$this->serverPort.']');

    }

    /**
     * 当request时调用
     * @param unknown $request
     * @param unknown $response
     */
    public function onRequest($request, $response)
    {
        //处理页面数据
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            return $response->end('');
        }
        $mimeList = (new \Config\Mime())->mimeList;

        $pathInfo = $request->server['path_info'];
        $requestUri = $request->server['request_uri'];
        $path_parts = pathinfo($pathInfo);

        if(isset($path_parts['extension']) && !empty($path_parts['extension']) && isset($mimeList[$path_parts['extension']]))
        {
            $filePath = __DIR__.'/Web/'.trim($pathInfo,"/");
            //file_put_contents('/tmp/swoole.log',"\r\n 【filePath】".$filePath,FILE_APPEND);
            if(file_exists($filePath)){
                $response->status(200);
                $response->header("Content-Type", $mimeList[$path_parts['extension']]);
                $fileContent = file_get_contents($filePath);
                $response->write($fileContent);
            } else {
                $response->status(404);
                $response->write('file notFound');
            }
            return $response->end();
        }


        if (isset($request->get)) {
            $_GET = $request->get;
        }
        if (isset($request->post)) {
            $_POST = $request->post;
        }
        if (isset($request->cookie)) {
            $_COOKIE = $request->cookie;
        }

        //file_put_contents('/tmp/swoole.log',"\r\n 【server】".print_r($request->server,true),FILE_APPEND);

        //$response->header("Content-Type",'text/html');
        $response->header("Content-Type", "text/html;charset=utf-8");
        $response->header('Connection','keep-alive');

        //处理php的逻辑
        try {
            ob_start();
            include __DIR__.'/Web/index.php';
            $result = ob_get_contents();
            ob_end_clean();
            if($response){
                $response->header("Content-Type", "text/html;charset=utf-8");
                $result = empty($result) ? '' : $result;
                $response->status(200);
                $response->end($result);
            }
            unset($result);
        } catch (Exception $e) {
            $response->status(500);
            $result = $e->getMessage();
            $response->end($result);
            unset($result);
        }
    }

    /**
     * 致命错误处理
     */
    public function handleFatal()
    {
        $error = error_get_last();
        if (isset($error['type'])) {
            switch ($error['type']) {
                case E_ERROR :
                    $severity = 'ERROR:Fatal run-time errors. Errors that can not be recovered from. Execution of the script is halted';
                    break;
                case E_PARSE :
                    $severity = 'PARSE:Compile-time parse errors. Parse errors should only be generated by the parser';
                    break;
                case E_DEPRECATED:
                    $severity = 'DEPRECATED:Run-time notices. Enable this to receive warnings about code that will not work in future versions';
                    break;
                case E_CORE_ERROR :
                    $severity = 'CORE_ERROR :Fatal errors at PHP startup. This is like an E_ERROR in the PHP core';
                    break;
                case E_COMPILE_ERROR :
                    $severity = 'COMPILE ERROR:Fatal compile-time errors. This is like an E_ERROR generated by the Zend Scripting Engine';
                    break;
                default:
                    $severity = 'OTHER ERROR';
                    break;
            }
            $message = $error['message'];
            $file = $error['file'];
            $line = $error['line'];
            $log = "$message ($file:$line)\nStack trace:\n";
            $trace = debug_backtrace();
            foreach ($trace as $i => $t) {
                if (!isset($t['file'])) {
                    $t['file'] = 'unknown';
                }
                if (!isset($t['line'])) {
                    $t['line'] = 0;
                }
                if (!isset($t['function'])) {
                    $t['function'] = 'unknown';
                }
                $log .= "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) && is_object($t['object'])) {
                    $log .= get_class($t['object']) . '->';
                }
                $log .= "{$t['function']}()\n";
            }
            if (isset($_SERVER['REQUEST_URI'])) {
                $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
            }
            file_put_contents('/tmp/data/web_error.log', $log);
        }
    }

    public function run($ip="0.0.0.0", $port=55757)
    {
        $this->serverHost = $ip;
        $this->serverPort = $port;
        $webServer = new \swoole_http_server($ip, $port);
        $webServer->set(
            array(
                'worker_num'    => 1,   //工作进程数量
                'max_request'   => 1, //多少次调用后再重启新的进程
                'daemonize' => true,
                //'log_file' => '/tmp/swoole.log',
            )
        );
        $webServer->on('WorkerStart', array($this, 'onWorkerStart'));
        $webServer->on('request', array($this, 'onRequest'));
        $webServer->on('start', array($this, 'onStart'));
        $webServer->on('ManagerStart', array($this,'onManagerStart'));
        $webServer->start();
    }
}

(new StatisticsWebServer())->run("0.0.0.0", 55757);