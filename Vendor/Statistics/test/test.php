<?php
include_once __DIR__.'/../StatisticClient.php';

//$project_name = 'my_test_project';
//$class_name = 'my_class';
//$function_name = 'my_function';
//$cost_time = mt_rand(1,20);
//$is_success=true;
//$code=200;
//$msg='mwq_test';


$project_name = 'Myserver';
$class_name = 'Test';
$function_name = 'testDb';
$args = array('id' => '1');
$cost_time = mt_rand(1,20);
$is_success=true;
$code=200;
$msg='mwq_test';
\Statistics\StatisticClient::config('10.211.55.7:55656');
for($i=1;$i<5;$i++)
{
    \Statistics\StatisticClient::serviceApiReport($project_name, $class_name, $function_name,$args, $cost_time, $is_success, $code, $msg);
}


//$project_name   = 'my_test_project';
//$class_name     = 'my_class';
//$function_name  = 'my_function';
//$cost_time      = mt_rand(1,20);
//$is_success     = true;
//$code           = 200;
//$msg            = 'mwq_test';
//
//\Statistics\StatisticClient::serviceApiReport($project_name, $class_name, $function_name, $cost_time, $is_success, $code, $msg);