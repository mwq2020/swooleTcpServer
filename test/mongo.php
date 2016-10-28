<?php

try{
    $conn = new MongoClient("10.211.55.7:27017"); #连接指定端口远程主机

    //获取当前示例里面的数据库
    //$dbs = $conn->listDBs();
    //print_r($dbs);

//    $data = array(
//        'module'    => 'club',
//        'interface' => 'getClubList',
//        'cost_time' => '0.05',
//        'success'   => true,
//        'time'      => date('Y-m-d H:i:s'),
//        'code'      => 200,
//    );
//
//    $module     = $data['module'];
//    $interface  = $data['interface'];
//    $cost_time  = $data['cost_time'];
//    $success    = $data['success'];
//    $time       = $data['time'];
//    $code       = $data['code'];

//    $db = $conn->selectDB($module);
//    $collection = $db->selectCollection($interface);
//    $flag = $collection->insert($data);
//    if(!empty($flag)){
//        echo "入库成功\r\n";
//    } else {
//        echo "入库失败\r\n";
//    }
//    var_dump($flag);


    $dbList = $conn->listDBs();
    print_r($dbList);

    $db = $conn->selectDB('MyServer');

//    $collections = $db->getCollectionNames();
//    foreach ($collections as $collection) {
//        echo "amount of documents in $collection: ";
//        //echo $collection->count(), "\n";
//    }
    $collection = $db->selectCollection('Test');


    //print_r($conn->getConnections());
    //print_r($collection->find());


    $keys = array("class" => 1);
    $initial = array("functions" => array());
    $reduce = "function (obj, prev) { prev.items.push(obj.function); }";
    $g = $collection->group($keys, $initial, $reduce);
    //echo json_encode($g['retval']);

} catch(exception $e){
    echo ''.$e;
}