<?php
namespace PHPClient;

class Rpc
{
    public static $instance;
    public static $autoException;
    public $logDir = '/tmp/rpc_client.log';

    public $configName; //要访问的服务名
    public $className;   //要访问的类名
    public $allConfig;

    public function __construct($configName)
    {
        $this->configName = $configName;
        return $this;
    }

    public function __call($method, $arguments)
    {
        $receiveData = null;
        try {
            $uriAddress = HostSwitch::getInstance($this->configName)->getOneUsableAddress();
            $client = new \swoole_client(SWOOLE_SOCK_TCP);
            if (!$client->connect($uriAddress['host'],$uriAddress['port'] , -1))
            {
                throw new \Exception("can not connect to[".$uriAddress['host'].':'.$uriAddress['port']."] failed. Error: {$client->errCode} \r\n");
            }
            $sendData = array('class'=>$this->className,'method'=>$method,'param_array'=>$arguments);
            $client->send(json_encode($sendData));
            $receiveData =  $client->recv();
            $client->close();
            if(empty($receiveData)){
                throw new \Exception('数据为空');
            }
            $receiveData = json_decode($receiveData,true);
            if(!is_array($receiveData)){
                throw new \Exception('数据解析失败');
            }

            if(empty($receiveData['flag']) || $receiveData['code'] != 200){
                throw new \Exception($receiveData['msg']);
            }
        } catch (\Exception $e){
            $error_msg = 'request:'.json_encode(array('class'=>$this->className,'method'=>$method,'params'=>$arguments))."\r\n";
            $error_msg .= date('Y-m-d H:i:s').'  error message info :'.$e->getMessage()."\r\n";
            file_put_contents($this->logDir,$error_msg,FILE_APPEND);
            if(self::$autoException){
                trigger_error($e);
            }
            $receiveData = array('code'=>500,'flag'=>false,'msg'=>$e->getMessage(),'data'=>array());
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
    public static function getInstance($configName,$autoException=false)
    {
        if(empty($configName)){
            throw new \Exception('configName 不能为空');
        }
        //是否自动抛出异常
        self::$autoException = $autoException;

        if (!self::$instance[$configName]) {
            self::$instance[$configName] = new Rpc($configName);
        }
        return self::$instance[$configName];
    }

    //设置调用的类名
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

}
