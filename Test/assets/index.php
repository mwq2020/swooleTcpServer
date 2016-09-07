<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
require_once  __DIR__.'/_init.php';



//自动加载lib库
 require_once TEST_ROOT_PATH.'/../../Vendor/Bootstrap/Autoloader.php';
\Bootstrap\Autoloader::instance()->addRoot(TEST_ROOT_PATH.'/../../Vendor/')->init();

// 检查是否登录
check_auth();

$func = isset($_GET['fn']) ? $_GET['fn'] : 'main';
$func = "\\TestClient\\Modules\\".$func;
if(!function_exists($func))
{
    foreach(glob(ST_ROOT . "/Modules/*") as $php_file)
    {
        require_once $php_file;
    }
}


if(!function_exists($func))
{
    $func = "\\TestClient\\Modules\\main";
}

$module = isset($_GET['module']) ? $_GET['module'] : '';
$interface = isset($_GET['interface']) ? $_GET['interface'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : strtotime(date('Y-m-d'));
$offset =  isset($_GET['offset']) ? $_GET['offset'] : 0;
call_user_func_array($func, array($module, $interface, $date, $start_time, $offset));
