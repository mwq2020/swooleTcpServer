<?php
namespace PHPClient;

class Rpc
{
    public static $instance;
    public static $autoException;
    public $logDir = '/tmp/rpc_client.log';

    public $configName;  //要访问的服务名
    public $className;   //要访问的类名
    public $allConfig;   //所有的配置数据

    public static $fixUri = true; //设置从固定的接口机器【true每次固定访问接口机器 false每次调用接口都随机访问接口机器
    public $uriInfo;

    private function __construct($configName)
    {
        $this->configName = $configName;
        return $this;
    }

    public function __call($method, $arguments)
    {
        $receiveData = null;
        try {
            if(self::$fixUri === false || empty($this->uriInfo)){
                $uriAddress = HostSwitch::getInstance($this->configName)->getOneUsableAddressInfo();
                $this->uriInfo = $uriAddress;
            } else {
                $uriAddress = $this->uriInfo;
            }

            //todo 此处需要添加校验 方便业务查找访问来源.
            $userName = isset($uriAddress['user']) ? $uriAddress['user'] : '';
            $sendData = array('user'=>$userName,'class'=>$this->className,'method'=>$method,'param_array'=>$arguments);

            if(extension_loaded('swoole')){
                $receiveData = $this->getSwooleData($uriAddress,$sendData);
            } elseif (extension_loaded('sockets')) {
                $receiveData = $this->getSocketData($uriAddress,$sendData);
            } else {
                throw new \Exception('PHP缺少swoole或者sockets的扩展');
            }

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

    /*
     * 开启单例模式
     */
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

    /*
     * 设置调用的类名
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * 如果没安装swoole，通过socket获取数据.
     * @param $uriAddress
     * @param $sendData
     * @return string
     * @throws \Exception
     */
    private function getSocketData($uriAddress,$sendData)
    {
        $address = $uriAddress['host'].':'.$uriAddress['port'];
        $conn = stream_socket_client("tcp://{$address}", $err_no, $err_msg);
        if(!$conn)
        {
            throw new \Exception("can not connect to $address , $err_no:$err_msg");
        }
        stream_set_blocking($conn, true);
        stream_set_timeout($conn, 4);
        $sendData = json_encode($sendData);
        if(fwrite($conn, $sendData) !== strlen($sendData)) {
            throw new \Exception('Can not send data');
        }

        return fgets($conn);
    }

    /**
     * 通过swoole客户端和api服务器交互数据。
     * @param $uriAddress
     * @param $sendData
     * @return mixed
     * @throws \Exception
     */
    private function getSwooleData($uriAddress,$sendData)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);
        if (!$client->connect($uriAddress['host'],$uriAddress['port'] , -1))
        {
            throw new \Exception("can not connect to[".$uriAddress['host'].':'.$uriAddress['port']."] failed. Error: {$client->errCode} \r\n");
        }
        $client->send(json_encode($sendData));
        $receiveData =  $client->recv();
        $client->close();
        return $receiveData;
    }

}
