<?php
namespace Controller;

class Test extends \Framework\CController
{
    //调试页面入口
    public function actionIndex()
    {

        $config = (array) new \Config\Test();
        $this->assign('apiList',$config['apiList']);
        $this->assign('requestData',$_POST);

        //默认页面
        if(empty($_POST)){
            return $this->display('index');
        }

        //处理数据后的页面
        $time_start = microtime(true);

        \PHPClient\HostSwitch::config($config['apiList']);
        $methodName = $_POST['function_name'];

        $serviceobj = \PHPClient\Rpc::getInstance($_POST['rpc_name'])->setClassName($_POST['class_name']);
        $res = call_user_func_array(array($serviceobj,$methodName),$_POST['argv']);
        $this->assign('service_data',$res);

        $time_end =  microtime(true);
        $used_time = $time_end - $time_start;
        $costtime = $used_time ? round($used_time, 6) : '';
        $this->assign('costtime',$costtime);
        $this->assign('time_start',$time_start);
        $this->assign('time_end',$time_end);

        //调取接口操作
        return $this->display('index');
    }

    //获取列表数据
    public function actionClasslist()
    {
        //$this->template->useLayout = false;
        $rpcName = $_POST['rpc_name'];
        $service_dir = dirname(dirname(__DIR__)).'/'.$rpcName.'/Handler';
        $data = array();
        if(is_dir($service_dir)) {
            foreach (glob($service_dir . "/*.php") as $php_file) {
                $service_name = basename($php_file, '.php');
                if (empty($service_name)) {
                    continue;
                }
                require_once $php_file;
                $data[$service_name] = array();
                //$class_name = "\\{$rpc_name}\\{$service_name}";
                $class_name = "\\Handler\\{$service_name}";
                if (class_exists($class_name)) {
                    $re = new \ReflectionClass($class_name);
                    // $method_array = $re->getMethods(\ReflectionMethod::IS_STATIC);
                    $method_array = $re->getMethods(\ReflectionMethod::IS_PUBLIC);
                    //$method_array = $re->getMethods(ReflectionMethod::IS_PUBLIC);(如果声明类的时候用的都是public那就用这个取)
                    // IS_PROTECTED IS_PRIVATE IS_ABSTRACT IS_FINAL
                    foreach ($method_array as $m) {
                        $method_name = $m->name;

                        $params = array();
                        $params_arr = $m->getParameters();
                        foreach ($params_arr as $p) {
                            $params[] = $p->name;
                        }
                        $data[$service_name][$method_name] = $params;
                    }
                }
            }
        }
        return $this->exitOut(json_encode($data));
    }

}