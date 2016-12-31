<?php
include_once __DIR__.'/../StatisticClient.php';

$project_name = 'my_test_project';
$class_name = 'my_class';
$function_name = 'my_function';
$cost_time = mt_rand(1,20);
$is_success=true;
$code=200;
$msg='mwq_test';

\Statistics\StatisticClient::serviceApiReport($project_name, $class_name, $function_name, $cost_time, $is_success, $code, $msg);


//$project_name   = 'my_test_project';
//$class_name     = 'my_class';
//$function_name  = 'my_function';
//$cost_time      = mt_rand(1,20);
//$is_success     = true;
//$code           = 200;
//$msg            = 'mwq_test';
//
//\Statistics\StatisticClient::serviceApiReport($project_name, $class_name, $function_name, $cost_time, $is_success, $code, $msg);