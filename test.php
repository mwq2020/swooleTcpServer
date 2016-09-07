<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);


class WebSocketClient
{

    public static $instance;
    private $application;
    public $logDir = '/tmp/swoole.log';
    public $swoole_clinet;
    public $className;
    public function __construct()
    {
        include_once __DIR__.'/Vendor/Bootstrap/Autoloader.php';
        \Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/')->init();
        $client = new swoole_client(SWOOLE_SOCK_TCP);
        //$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        //$client->on('connect',array($this,'onConnect'));
        //$client->on('receive',array($this,'onReceive'));

        //$client->on('error',array($this,'onError'));
        //$client->on('close',array($this,'onClose'));
        $this->swoole_clinet = $client;
        return $this;
    }

    public function __call($method, $arguments)
    {
        $receiveData = null;
        try {
            $client = $this->swoole_clinet;
            if (!$client->connect('127.0.0.1', 7000, -1))
            {
                throw new Exception("connect failed. Error: {$client->errCode}");
            }
            $sendData = array('class'=>$this->className,'method'=>$method,'param_array'=>$arguments);
            $client->send(json_encode($sendData));
            $receiveData =  $client->recv();
            $client->close();
        } catch (\Exception $e){
            throw new \Exception($e);
        }
        return $receiveData;
    }


    // 发送数据的时候回调的时候回调[模拟回调]
    public function oneSend()
    {

    }

    // 客户端连接服务器成功后会回调此函数。[自带回调]
    public function onConnect()
    {

    }

    // 接收数据的时候回调[自带回调]异步时调用
    public function onReceive($cli, $data)
    {
        return $data;
    }

    //连接服务器失败时会回调此函数。 UDP客户端没有onError回调[自带回调]
    public function onError()
    {

    }

    // 连接被关闭时回调此函数。[自带回调]
    public function onClose()
    {

    }

    //开启单例模式
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new WebSocketClient;
        }
        return self::$instance;
    }

    //设置调用的类名
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

}


$params = array();
$params['a'] = 'hello word!';
$params['b'] = 'mwq test';
$params['c'] = time();

$res  = WebSocketClient::getInstance()->setClassName('Test')->testEcho($params);
print_r(json_decode($res,true));
exit;




