<?php
/**
 * php5下面使用mongo操作
 */

namespace Mongo;
class Connection
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
            self::$instance[$confkey] = new Connection(self::$_config[$confkey]);
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
     * 初始化mongodb 的Manager
     * @param array $conf
     */
    private function __construct(array $conf)
    {
        $this->_conn = new \MongoClient($conf['url']);
        $this->_db   = $conf['dbname'];
    }

    /**
     * 插入数据记录
     * @param $collectionName
     * @param array $data
     * @return mixed
     */
    public function insert($collectionName,array $data)
    {
        $db = $this->_conn->selectDB($this->_db);
        $collection = $db->selectCollection($collectionName);
        $collection->insert($data);
    }


    function query($collname,array $filter, array $options)
    {

    }

    /**
     * 获取当前mongoDB句柄
     * @return
     */
    public function getMongoConnection()
    {
        return $this->_conn;
    }

}
