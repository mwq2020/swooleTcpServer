<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

include_once __DIR__.'/../../../Vendor/Bootstrap/Autoloader.php';
\Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/../../../Vendor/')->init();


require_once __DIR__ . "/vendor/autoload.php";

$manager = new \MongoDB\Driver\Manager("mongodb://localhost:27017");
$collection = new \MongoDB\Collection($manager, "db.test");


// 读取一条数据
$data = $collection->findOne(array('id' => 1));

// 读取多条数据
$options = array(
'projection' => array('id' => 1, 'age' => 1, 'name' => 1), // 指定返回哪些字段
'sort' => array('id' => -1), // 指定排序字段
'limit' => 10, // 指定返回的条数
'skip' => 0, // 指定起始位置
);
$dataList = $collection->find(array('age' => 50), $options);

// 插入一条数据
$data = array('id' => 2, 'age' => 20, 'name' => '张三');