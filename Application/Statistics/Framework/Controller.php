<?php
/**
 * 框架的控制器基类
 */
namespace Framework;
class Controller
{
    public $request;
    public $response;
    public $template;//模板处理类

    //自定义的全局变量
    public $_controllerName;
    public $_actionName;

    public $_defaultControllerName;
    public $_defaultActionName;

    /**
     * 框架的执行入口
     */
    public function Run()
    {
        $requestUri = $this->request->server['request_uri'];
        $this->processRoute($requestUri);
        echo $this->dealRequest();
    }

    /**
     * 处理所有的请求分发
     * @param $request_uri
     */
    public function dealRequest()
    {
        try {
            //ob_start();
            $controllerSpaceName = "\\Controller\\{$this->_controllerName}";
            $controller = new $controllerSpaceName;
            $controller->request = $this->request;
            $controller->response = $this->response;
            $controller->controllerName = $this->_controllerName;
            $controller->actionName     = $this->_actionName;
            $controller->actionShortName     = $this->_actionShortName;
            return call_user_func(array($controller, $this->_actionName));

            //$content = ob_get_contents();
            //ob_clean();
            //return $content;
        } catch (\Exception $e) {
            return '<br>处理路由时发生错误：<br>'.$e;
        }
        return 'success!';
    }

    /**
     * 处理动态程序的路由
     * @param $uri
     * @return mixed
     */
    public function processRoute($uri)
    {
        $controllerName = "Index";
        $actionName = "Index";
        if(empty($uri) || trim($uri) == '/'){
            $this->_controllerName = $controllerName;
            $this->_actionName = 'action'.$actionName;
            $this->_actionShortName = strtolower($actionName);
        } else {
            $uris = explode('/',trim($uri,'/'));
            $this->_controllerName = ucfirst($uris[0]);
            if(count($uris) > 1){
                $this->_actionName = 'action'.ucfirst($uris[1]);
                $this->_actionShortName = strtolower($uris[1]);
            } else {
                throw new \Exception('路由解析错误');
            }
        }
        return true;
    }

}