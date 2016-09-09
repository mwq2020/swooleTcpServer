<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

include_once __DIR__.'/Vendor/Bootstrap/Autoloader.php';
\Bootstrap\Autoloader::instance()->addRoot(__DIR__.'/')->init();

//$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testDb();
//print_r($res);

$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testEcho('a','b','c');
print_r($res);

$res = \PHPClient\Rpc::getInstance('ClubService')->setClassName('Test')->testDb();
print_r($res);
exit;




