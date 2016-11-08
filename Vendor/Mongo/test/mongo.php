<?php

include __DIR__.'/../../Bootstrap/Autoloader.php';
\Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/../../')->init();

$config = array(
    'default' => array(
        'uri'  => '127.0.0.1',
        'port' => 27017
    ),
);


\Mongo\HostSwitch::config($config);
//$config = \Mongo\HostSwitch::instance()->getConfig();
//print_r($config);
//exit;

$mongo = \Mongo\Client::instance('default',true);
$dbList = $mongo->listDBs();
//print_r($dbList);
//exit;


//$list = $mongo->selectDB('MyServer')->selectCollection('Test')->find();
//var_dump($list);


$collectionList = $mongo->selectDB('MyServer')->listCollections();
var_dump($collectionList);
foreach($collectionList as $collection)
{
    echo $collection."\r\n";
}

//$collectionNameList = $mongo->selectDB('MyServer')->getCollectionNames();
//var_dump($collectionNameList);


$collectionInfo = $mongo->selectDB('MyServer')->getCollectionInfo();
print_r($collectionInfo);



