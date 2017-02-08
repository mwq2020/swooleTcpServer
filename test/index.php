<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

include_once __DIR__.'/../Vendor/Bootstrap/Autoloader.php';
\Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/../Vendor/')->init();

//$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testDb();
//print_r($res);

//$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testEcho('a','b','c');
//print_r($res);
//
//$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testDb();
//print_r($res);

$config = array(
    'ClubService' => array(
        'uri' => "127.0.0.1:7000",
        'user' => 'club_manage',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    )
);

\PHPClient\HostSwitch::config($config);

//$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testException();
//$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testEcho('a','b','c');
//print_r($res);

//统计代码的引入
if(true){
//public $default = array(
//    'uri' => '127.0.0.1:55656',
//    );
    \Statistics\StatisticClient::config('127.0.0.1:55656');
    $project_name = 'MyServer';
    $class_name = 'Test';
    $function_name = 'testDb';
    $args = json_encode(array('id' => '1'));
    $cost_time = mt_rand(1,20)/100;
    $is_success=false;
    $code=500;
    $msg='mwq_test';
    $flag = \Statistics\StatisticClient::serviceApiReport($project_name, $class_name, $function_name,$args, $cost_time, $is_success, $code, $msg);
    var_dump($flag);
}

