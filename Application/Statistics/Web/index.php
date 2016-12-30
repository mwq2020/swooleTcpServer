<?php
/**
 * 统计页面的入口
 * 由于此页面是用的swoole做为服务器，所以不能使用 exit和die等直接导致程序中断的方法
 * 否则将导致进程异常退出，得不到想要的页面效果
 * exit和die可想办法用return等语句替代
 */

$obj = new \Framework\Controller();
$obj->request = $request;
$obj->response = $response;
$obj->run();

























