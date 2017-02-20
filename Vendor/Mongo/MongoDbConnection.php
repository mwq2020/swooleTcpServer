<?php
/**
 * php7用mongodb库使用mongo功能
 */


namespace Mongo;
class MongoDbConnection
{

    private static $instance     = [];
    private $_conn          = null;
    private $_db            = null;
    private static $_config = [];

    /**
     * 创建
     * @param string $confkey
     * @return \m_mgdb
     */
    public static function instance($confkey)
    {
        if (!$confkey) {
            throw new \Exception('mongo配置key不能为空');
        }

        if(empty(self::$_config)){
            self::config();
        }

        if(!isset(self::$_config[$confkey]) || empty(self::$_config[$confkey])){
            throw new \Exception('mongo配置key['.$confkey.']内容为空');
        }

        if (!isset(self::$instance[$confkey])) {
            self::$instance[$confkey] = new MongoDbConnection(self::$_config[$confkey]);
        }
        return self::$instance[$confkey];
    }

    /**
     * 获取配置(初始化配置或者自动获取配置)
     * @param string $config
     */
    public static function config($config=array())
    {
        if(!empty($config)){
            self::$_config = $config;
        } else {
            self::$_config = (array) new \Config\Mongo();
        }
        return self::$_config;
    }

    /**
     * 获取读写链接
     * @return \MongoDB\Driver\WriteConcern
     */
    public static function writeConcern()
    {
        return new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
    }

    /**
     * 初始化mongodb 的Manager
     * @param array $conf
     */
    private function __construct(array $conf)
    {
        $this->_conn = new \MongoDB\Driver\Manager($conf['url']);
        $this->_db   = $conf['dbname'];
    }

    /**
     * 插入数据记录
     * @param $collectionName
     * @param array $data
     * @return mixed
     */
    function insert($collectionName,array $data)
    {
        $bulk = new \MongoDB\Driver\BulkWrite();
        foreach($data as $v){
            if(is_array($v)){
                $bulk->insert($v);
            }
        }
        $manager = $this->_conn;
        $writeConcern = self::writeConcern();
        return $manager->executeBulkWrite($this->_db.'.'.$collectionName, $bulk, $writeConcern);
    }


    function command(array $param)
    {
        $cmd = new \MongoDB\Driver\Command($param);
        return $this->_conn->executeCommand($this->_db, $cmd);
    }

    function query($collectionName,array $filter, array $options)
    {
        $query = new \MongoDB\Driver\Query($filter, $options);
        return $cursor = $this->_conn->executeQuery("{$this->_db}.{$collectionName}", $query);
    }

    /**
     * 获取当前mongoDB句柄
     * @return
     */
    function getMongoManager()
    {
        return $this->_conn;
    }

}