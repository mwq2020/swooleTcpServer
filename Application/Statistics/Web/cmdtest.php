<?php
/**
 * 本程序供windows、linux作为接口的测试终端调试用
 * 本程序只能作为本机接口调试测试
 * 使用时在cmd、终端执行 /path/to/php -S 127.0.0.1:8080 -t /path/to/index.php
 * 在浏览器中输入 127.0.0.1:8080 即可以看到调试终端的页面 选择自己的接口类及方法 输入参数即可开始调试
 */

if(empty($_POST)){
    $serviceList = get_project_list();

    if(isset($_GET['action']) && $_GET['action'] == 'get_class'){
        $function_list = get_project_function($_GET['rpc_name']);
        exit(json_encode($function_list));
    }

    include '../Views/cmdtest/test.php';
} else {
    $service_name   = isset($_POST['rpc_name']) ? $_POST['rpc_name'] : '';
    $class_name     = isset($_POST['class_name']) ? $_POST['class_name'] : '';
    $function_name  = isset($_POST['function_name']) ? $_POST['function_name'] : '';
    $argvs          = isset($_POST['argv']) ? $_POST['argv'] : '';

    if($argvs){
        foreach($argvs as $row_key => $row_argv){
            if(is_scalar($row_argv) && strpos($row_argv,'array') === 0){
                eval('$argvs['.$row_key.'] = '.$row_argv.';');
            }
        }
    }
    $time_start = microtime(true);
    try {
        $service_data = get_service_data($service_name,$class_name,$function_name,$argvs);
    } catch(\Exception $e)  {
        $service_data = ''.$e;
    } catch(exception $e){
        $service_data = ''.$e;
    }

    $serviceList = get_project_list();

    $time_end = microtime(true);
    $cost_time = $time_end-$time_start;
    include '../Views/cmdtest/test.php';
}


function get_project_list()
{
    $serviceList = array();
    $service_dir = dirname(dirname(__DIR__));
    foreach(glob($service_dir."/*") as $serviceDir){
        $serviceName = basename($serviceDir);
        if(!in_array($serviceName,array('TestClient','Statistics'))){
            array_push($serviceList,$serviceName);
        }
    }
    return $serviceList;
}

function get_project_function($projectName)
{
    $service_dir = dirname(dirname(__DIR__))."/".$projectName."/Handler";
    $data = array();
    if(is_dir($service_dir)){
        foreach (glob($service_dir."/*.php") as $php_file) {
            $service_name = basename($php_file, '.php');
            if(empty($service_name)){ continue; }
            require_once $php_file;
            $data[$service_name] = array();

            $class_name = "\\Handler\\{$service_name}";
            if(class_exists($class_name))
            {
                $re = new \ReflectionClass($class_name);
                // $method_array = $re->getMethods(\ReflectionMethod::IS_STATIC);
                $method_array = $re->getMethods(\ReflectionMethod::IS_PUBLIC);
                //$method_array = $re->getMethods(ReflectionMethod::IS_PUBLIC);(如果声明类的时候用的都是public那就用这个取)
                // IS_PROTECTED IS_PRIVATE IS_ABSTRACT IS_FINAL
                foreach($method_array as $m)
                {
                    $method_name = $m->name;

                    $params = array();
                    $params_arr = $m->getParameters();
                    foreach($params_arr as $p)
                    {
                        $params[] = $p->name;
                    }
                    $data[$service_name][$method_name] = $params;
                }
            }
        }
    }
    return $data;
}

function get_service_data($service_name,$class_name,$function_name,$args)
{
    //设置自动加载的目录
    $service_dir = dirname(dirname(__DIR__))."/".$service_name;
    include_once $service_dir . '/../../Vendor/Bootstrap/Autoloader.php';
    \Bootstrap\Autoloader::instance()->addRoot($service_dir.'/')->addRoot($service_dir.'/../../Vendor/')->init();

    // 判断数据是否正确
    if(empty($class_name) || empty($function_name) || !isset($args))
    {
        throw new \Exception('参数不能为空！');
    }

    // 判断类对应文件是否载入
    $class_name = "\\Handler\\{$class_name}";
    $include_file = $service_dir . "/Handler/$class_name.php";
    if(is_file($include_file))
    {
        require_once $include_file;
    }

    //判断类存在与否
    if(!class_exists($class_name))
    {
        throw new \Exception("class $class_name not found");
    }

    //判断方法存在与否
    $obj_class = new $class_name;
    if(!method_exists($obj_class,$function_name)){
        throw new \Exception('类【'.$class_name.'】不包含方法【'.$function_name.'】');
    }

    // 发送数据给客户端，调用成功，data下标对应的元素即为调用结果
    $ret = call_user_func_array(array($obj_class, $function_name), $args);
    return $ret;
}

