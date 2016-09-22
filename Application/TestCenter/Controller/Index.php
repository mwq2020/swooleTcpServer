<?php

namespace Controller;

class Index extends Base
{

    public function actionIndex()
    {
        /*
        echo "<pre>";
        var_dump($this->controllerName);
        var_dump($this->request);
        var_dump($this->templatePath);
        */

        $config = (array) new \Config\Test();
        $this->assign('apiList',$config['apiList']);

        $this->display();
    }

    //获取类名列表
    public function actionClasslist()
    {
        $this->template->useLayout = false;
        $rpcName = $this->request['rpc_name'];
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
        echo json_encode($data);
        return true;
    }

    //获取类里面包含的方法列表
    public function actionMethodlist()
    {
        $this->template->useLayout = false;
        $data = array('aaaa'=>'bbbb','ccc'=>'dddd');
        echo json_encode($data);
        return true;
    }

    //获取接口调用结果
    public function actionGetresult()
    {
        $config = (array) new \Config\Test();
        $this->assign('apiList',$config['apiList']);
        $this->assign('requestData',$this->request);

        $time_start = microtime(true);

        \PHPClient\HostSwitch::config($config['apiList']);
        $methodName = $this->request['function_name'];

        $serviceobj = \PHPClient\Rpc::getInstance($this->request['rpc_name'])->setClassName($this->request['class_name']);
        $res = call_user_func_array(array($serviceobj,$methodName),$this->request['argv']);
        $this->assign('service_data',$res);

        $time_end =  microtime(true);
        $used_time = $time_end - $time_start;
        $costtime = $used_time ? round($used_time, 6) : '';
        $this->assign('costtime',$costtime);
        $this->assign('time_start',$time_start);
        $this->assign('time_end',$time_end);

        //调取接口操作
        $this->display('/index/index.php');
        return '';
    }

    //测试页面
    public function actionTest()
    {
        /*
        echo "<pre>";
        var_dump($this->controllerName);
        var_dump($this->request);
        var_dump($this->templatePath);
        */
        $this->display();
    }

}