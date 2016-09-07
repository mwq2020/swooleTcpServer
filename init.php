<?php

$serv = new swoole_server("127.0.0.1", 9501);
$serv->set(array(
    'worker_num'    => 4,   //工作进程数量
    'max_request'   => 2, //多少次调用后再重启新的进程
    'daemonize'     => true, //是否作为守护进程
    // 'daemonize'     => false, //是否作为守护进程
));

$serv->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});

$serv->on('receive', function ($serv, $fd, $from_id, $data) {
    //print_r($data);
    // @unpack('N', $data);
    //$data_unpack = @unpack("Ndata_length", $data);
    //$dataContent = substr($data, -$data_unpack['data_length']);
    //print_r($dataContent);
    $dealHandler = new dealHandler;
    $ret = $dealHandler->dealBusiness($data);
    $data = json_encode($ret);
    //echo "\r\n". $data;
    $serv->send($fd, $data);
    $serv->close($fd);
});
$serv->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

$serv->on('start',function($serv){
   swoole_set_process_name('master swoole bestdo init.php'); //可以甚至swoole的进程名字 用于区分是否是master进程 {设置主进程的名称}
});
$serv->on('managerStart',function($serv){
   swoole_set_process_name('running manager swoole bestdo init.php'); //可以甚至swoole的进程名字 用于区分是否是master进程 {设置管理进程的名称}
});
$serv->on('WorkerStart',function($serv){
   swoole_set_process_name('running worker swoole bestdo  init.php'); //可以甚至swoole的进程名字 用于区分是否是master进程 {设置worker进程的名称}
});


$serv->start();



//处理业务逻辑
class dealHandler 
{
    public function checkInData($data)
    {

    }

    public function dealBusiness($data)
    {
        defined('VENDOR_ROOT') || define('VENDOR_ROOT', __DIR__ . '/Vendor/');
        require_once VENDOR_ROOT . 'Bootstrap/Autoloader.php';
        \BootStrap\Autoloader::instance()->init();

        $data = json_decode($data,true);
        //print_r($data);
        $class = $data['class'];
        $method = $data['method'];
        $param_array = $data['param_array'];

        $ret_data = array();

        // 判断类对应文件是否载入
        $class_name = "\\Handler\\{$class}";
        if(!class_exists($class_name))
        {
            $include_file = __DIR__ . "/Handler/$class.php";
            if(is_file($include_file))
            {
                require_once $include_file;
            }
        }

        // 调用类的方法
        try
        {
            //判断类存在与否
            if(!class_exists($class_name))
            {
                throw new \Exception('类【'.$class.'】不存在' ,'500');
                $code = 404;
                $msg = "class $class not found";
                $ret_data = array('code'=>$code, 'flag'=>false,'msg'=>$msg, 'data'=>null);
            }

            //判断方法存在与否
            $obj_class = new $class_name;
            if(!method_exists($obj_class,$method)){
                throw new \Exception('类【'.$class.'】不包含方法【'.$method.'】' ,'500');
            }
            $ret = call_user_func_array(array($obj_class, $method), $param_array);
            // 发送数据给客户端，调用成功，data下标对应的元素即为调用结果
            $ret_data = array('code'=>0, 'flag'=>true, 'msg'=>'ok', 'data'=>$ret);
        }
            // 有异常
        catch(\Exception $e)
        {
            // 发送数据给客户端，发生异常，调用失败
            $code = $e->getCode() ? $e->getCode() : 500;
            $ret_data = array('code'=>$code,'flag'=>false, 'msg'=>$e->getMessage(), 'data'=>$e);
        }

        return $ret_data;
    }

}


// require '../../config.php';
// import('#net.driver.SelectTCP');
// //协议对象，Swoole自带了一些协议如ChatServer，HttpServer等
// $protocol = new ChatServer;
// //这里可以替换成其他的驱动模式
// $server = new SelectTCP('localhost',$protocol->default_port);
// $server->setProtocol($protocol);
// $server->run();

// //协议类必须实现Swoole_TCP_Server_Protocol接口
// class ChatServer implements Swoole_TCP_Server_Protocol
// {
//     public $default_port = 8080;
//     //接收到数据时调用此函数
//     function onRecive($client_id,$data)
//     {
//          $this->server->send($client_id,"hello"); //向某个客户端发送信息
//          $this->server->sendAll("$client_id login"); //向全体客户端发送信息，BlockTCP不支持此方法
//     }
//     //服务器启动
//     function onStart()
//     {

//     }
//     //服务器关闭
//     function onShutdown()
//     {

//     }
//     //客户端关闭
//     function onClose($client_id)
//     {

//     }
//     //有客户端连接到服务器
//     function onConnect($client_id)
//     {

//     }
// }







// $serv = new swoole_server("127.0.0.1", 11000);
// $serv->set(array(
//     'worker_num' => 8,   //工作进程数量
//     'daemonize' => true, //是否作为守护进程
// ));
// $serv->on('connect', function ($serv, $fd){
//     echo "Client:Connect.\n";
// });
// $serv->on('receive', function ($serv, $fd, $from_id, $data) {
//     $serv->send($fd, 'Swoole: '.$data);
//     $serv->close($fd);
// });
// $serv->on('close', function ($serv, $fd) {
//     echo "Client: Close.\n";
// });
// $serv->start();