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

$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testException();
print_r($res);
