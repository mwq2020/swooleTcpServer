<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

include_once __DIR__.'/../../../Vendor/Bootstrap/Autoloader.php';
\Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/../../../Vendor/')->init();

//$manager = new \MongoDB\Driver\Manager("mongodb://127.0.0.1:27017");

$config = array(
    'default' => ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'default'],
    //'statistics' => ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'Statistics'],
    'statistics' => ['uri' => 'mongodb://10.211.55.7:27017','dbname' => 'Statistics'],
    'statisticsLog' => ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'StatisticsLog'],
);

\MongoDB\Client::config($config);
//$client = new \MongoDB\Client('default');
//$manager = $client->getManager();
//$collection = new \MongoDB\Collection($manager, 'StatisticsLog','MyServer');

$manager = (new \MongoDB\Client('statistics'))->getManager();

$start_timestamp = strtotime('2017-03-08 00:00:00');
$end_timestamp = strtotime('2017-03-08 23:59:59');

$where = array();
$where['time_stamp'] = array('$gte'=>$start_timestamp,'$lte'=>$end_timestamp);
$where['project_name'] = 'MyServer';

$options = array('skip' => 0);
$collection = new \MongoDB\Collection($manager, 'Statistics','MyServer');
$dataList = $collection->find($where, $options);
$list = array();
foreach($dataList as $row) {
    array_push($list,$row);
}

print_r($list);
exit;

// 插入一条数据
//$data = array('id' => 2, 'age' => 20, 'name' => '张三');
//$flag = $collection->insertOne($data);
//var_dump($flag);

//$data = $collection->findOne(array('name' => '张三'));
//var_dump($data);


// 读取多条数据
$options = array(
    //'projection' => array('id' => 1, 'age' => 1, 'name' => 1), // 指定返回哪些字段
    //'sort' => array('id' => -1), // 指定排序字段
    //'limit' => 10, // 指定返回的条数
    //'skip' => 0, // 指定起始位置
);
//$filter = array('function_name' => 'testDb');
$filter = array('function_name' => 'testException');
//$filter = array();


$list = array();
$dataList = $collection->find($filter, $options);
foreach($dataList as $row){
    $id = $row['_id']->__toString();
    //$row['id'] = $id;
    $list[$id] = $row;
//    $row['id'] = $id;
//    print_r($row);
//    echo (string) $row['_id'];
}

print_r($list);

//
//echo "\r\n";
//$count = $collection->count($filter);
//var_dump($count);

//ObjectId("589b2ac78ead0e6df20041a7")
//ObjectId("589b33418ead0ef3840041a7")
//查询单条数据记录
//$mongoId = '589b33418ead0ef3840041a7';
//$mongoIdObj = new \MongoDB\BSON\ObjectID($mongoId);
//$info = $collection->findOne(['_id'=>$mongoIdObj]);
//var_dump($info);
//var_dump($mongoIdObj->__toString());
exit;


$collectionListObj = (new \MongoDB\Database($manager,'StatisticsLog'))->listCollections();
$collectionList = array();
foreach($collectionListObj as $row){
    if($row->getName() == 'system.indexes'){
        continue;
    }
    array_push($collectionList,$row->getName());
}

print_r($collectionList);


$collection =  new \MongoDB\Collection($manager, 'StatisticsLog','MyServer');

$start_timestamp = strtotime('2017-01-21');
$end_timestamp = strtotime('2017-02-24');
$filter  = array(
    'time_stamp' => [
        '$gte' => $start_timestamp,
        '$lte' => $end_timestamp,
    ],
);
$count = $collection->count($filter);

$list = $collection->find($filter);
var_dump($list);
foreach($list as $key => $row){
    var_dump($key);
    var_dump($row);
    exit;
}









