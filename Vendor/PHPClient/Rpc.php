<?php
namespace PHPClient;

class Rpc
{
    public static $instance;
    public $logDir = '/tmp/rpc_client.log';
    public $swoole_clinet;
    public $className;
    public function __construct()
    {
        //$client = new swoole_client(SWOOLE_SOCK_TCP);
        //$this->swoole_clinet = $client;
        return $this;
    }

    public function __call($method, $arguments)
    {
        $receiveData = null;
        try {
            //$client = $this->swoole_clinet;
            $client = new \swoole_client(SWOOLE_SOCK_TCP);
            if (!$client->connect('127.0.0.1', 7000, -1))
            {
                throw new Exception("connect failed. Error: {$client->errCode}");
            }
            $sendData = array('class'=>$this->className,'method'=>$method,'param_array'=>$arguments);
            $client->send(json_encode($sendData));
            $receiveData =  $client->recv();
            $client->close();
            if(empty($receiveData)){
                throw new \Exception('数据为空');
            }
            $receiveData = json_decode($receiveData,true);
            if(empty($receiveData)){
                throw new \Exception('数据解析失败');
            }
            if($receiveData['flag'] == false || $receiveData['code'] != 200){
                throw new \Exception($receiveData['msg']);
            }
        } catch (\Exception $e){
            $error_msg = 'request:'.json_encode(array('class'=>$this->className,'method'=>$method,'params'=>$arguments))."\r\n";
            $error_msg .= date('Y-m-d H:i:s').' error info:'.$e->getMessage()."\r\n";
            file_put_contents($this->logDir,$error_msg,FILE_APPEND);
            throw new \Exception($e);
        }
        return $receiveData['data'];
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
            self::$instance = new Rpc;
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
