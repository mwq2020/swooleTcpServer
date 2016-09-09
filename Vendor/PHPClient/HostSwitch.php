<?php

namespace PHPClient;
class HostSwitch
{
    public static $instance;
    private static $config;
    public $configName; //要访问的服务名

    //开启单例模式
    public static function getInstance($configName)
    {
        if(empty($configName)){
            throw new \Exception('configName 不能为空');
        }

        if (!self::$instance[$configName]) {
            self::$instance[$configName] = new HostSwitch($configName);
        }
        return self::$instance[$configName];
    }

    public function __construct($configName)
    {
        $this->configName = $configName;
        return $this;
    }

    /**
     * 加载配置或自动查找配置
     * @param array $config
     */
    public static function config($config=array())
    {
        if(empty($config)){
            self::$config = (array) new \Config\PHPClient();
        } else {
            self::$config = $config;
        }
    }

    /**
     * 获取一个可用的配置通过配置名
     */
    public function getOneUsableAddress()
    {
        if(empty($this->configName)){
            throw new \Exception('配置名为空【'.__FUNCTION__.'】');
        }
        //如果配置不存在就载入配置
        if(!self::$config){
            self::config();
        }
        if(!isset(self::$config[$this->configName])){
            throw new \Exception('配置不存在，请检查配置是否存在或者预载入');
        }
        if(!isset(self::$config[$this->configName]['uri']) || empty(self::$config[$this->configName]['uri'])){
            throw new \Exception($this->configName.'配置uri不存在或者为空');
        }
        $uriList = self::$config[$this->configName]['uri'];
        if(is_array($uriList)){
            $uri = $uriList[array_rand($uriList)];
        } else {
            $uri = $uriList;
        }
        list($host,$port) = explode(':',$uri);
        return array('host'=>$host,'port'=>$port);
    }

}