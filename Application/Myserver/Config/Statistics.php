<?php
namespace Config;
$enviroment = isset($_SERVER['RUNTIME_ENVIROMENT']) ? $_SERVER['RUNTIME_ENVIROMENT'] : 'online';
include_once $enviroment.'/Statistics.php';