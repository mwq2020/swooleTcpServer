<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

include_once __DIR__.'/../../../Vendor/Bootstrap/Autoloader.php';
\Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/../../../Vendor/')->init();

//$manager = new \MongoDB\Driver\Manager("mongodb://127.0.0.1:27017");

$config = array(
    'default' => ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'default'],
    'statistics' => ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'Statistics'],
    'statisticsLog' => ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'StatisticsLog'],
);

\MongoDB\Client::config($config);
$client = new \MongoDB\Client('default');
$manager = $client->getManager();
$collection = new \MongoDB\Collection($manager, 'test','test');


// 插入一条数据
$data = array('id' => 2, 'age' => 20, 'name' => '张三');
$flag = $collection->insertOne($data);
var_dump($flag);


$data = $collection->findOne(array('name' => '张三'));
var_dump($data);


// 读取多条数据
$options = array(
    'projection' => array('id' => 1, 'age' => 1, 'name' => 1), // 指定返回哪些字段
    'sort' => array('id' => -1), // 指定排序字段
    'limit' => 10, // 指定返回的条数
    'skip' => 0, // 指定起始位置
);
$dataList = $collection->find(array('age' => 50), $options);
var_dump($dataList);

$count = $collection->count();
var_dump($count);


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

foreach ($list->listIndexes() as $indexInfo) {
    var_dump($indexInfo);
}








