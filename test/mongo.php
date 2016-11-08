<?php

try{
    //$conn = new MongoClient("10.211.55.7:27017"); #连接指定端口远程主机
    //$conn = new MongoClient("127.0.0.1:27017"); #连接指定端口远程主机

    $mongo = new MyMongo();
    //$mongo->group();
    $list = $mongo->find('MyServer','Test',array('function_name'=>'testDb',"success_count"=>array('$gt' => 1,'$lt' => 17)));
    print_r($list);

} catch(exception $e){
    echo ''.$e;
}



class MyMongo
{
    public $conn = null;
    public function __construct()
    {
        $this->conn = new MongoClient("127.0.0.1:27017"); #连接指定端口远程主机
    }

    public function group()
    {
        $db = $this->conn->selectDB('MyServer');
        $collection = $db->selectCollection('Test');
        $keys = array("class_name" => 1);
        $initial = array("function_names" => array());
        $reduce = "function (obj, prev) { prev.function_names.push(obj.function_name); }";
        $g = $collection->group($keys, $initial, $reduce);
        echo json_encode($g['retval']);
    }

    public function find($dbName,$collectionName,$where)
    {
        $collection = $this->conn->selectDB($dbName)->selectCollection($collectionName);
        $cursor = $collection->find($where);
        $result = array();
        while( $cursor->hasNext() ) {
            array_push($result,$cursor->getNext());
        }
        return $result;
    }
}
