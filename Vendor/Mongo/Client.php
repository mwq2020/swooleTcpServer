<?php
namespace Mongo;

class Client
{
    private static $instanceObj;
    private static $mongoClientObj = array();
    private static $config = array();
    public static $sourceName = '';

    //开启单例模式
    public static function instance($sourceName='default',$isMongoHander = false)
    {
        if(empty($sourceName)){
            throw new \Exception('mongoClient的sourceName 不能为空');
        }
        self::$sourceName = $sourceName;
        if(!self::$instanceObj){
            self::$instanceObj = new self;
        }
        if(!isset(self::$mongoClientObj[$sourceName])){
            $mongoHander = self::initMongo($sourceName);
        }
        if($isMongoHander){
            return self::$mongoClientObj[$sourceName];
        }
        //print_r(self::$mongoClientObj);exit;
        return self::$instanceObj;
    }

    private function __construct()
    {

    }

    private static function initMongo($sourceName)
    {
        $config = HostSwitch::instance()->getConfig();
        if(empty($config)){
            throw new \Exception('please use Mongo::config init configs');
        }
        if(isset($config[$sourceName]) || empty($sourceName) || empty($config[$sourceName])){
            self::$mongoClientObj[$sourceName] = new \MongoClient($config[$sourceName]['uri'].':'.$config[$sourceName]['port']);
        }
        return self::$mongoClientObj[$sourceName];
    }

    //加载配置
    public static function config(array $config)
    {
        if(empty($config)){
            self::$config = (array) new \Config\Mongo();
        } else {
            self::$config = $config;
        }
    }

    /**
     * 调用原生的mongo类
     * @param $name
     * @param $arguments
     */
    public function __call($name,$arguments)
    {
        $sourceName = self::$sourceName;
        $mongo = self::$mongoClientObj[$sourceName];
        return $mongo->$name($arguments);
    }

    public function selectDB($dbName)
    {
        $sourceName = self::$sourceName;
        return self::$mongoClientObj[$sourceName]->selectDb($dbName);
    }

    //关闭单个链接
    public function closeConnect()
    {

    }

    //关闭所有的mongo链接
    public function closeAllConnect()
    {

    }

}