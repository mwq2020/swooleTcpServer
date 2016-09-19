<?php
namespace Test\Model;

class Request extends Base
{
    public $request;
    public $server;

    /**
     * 处理所有的请求分发
     * @param $request_uri
     */
    public function dealRequest($request)
    {
        $this->request = parse_str($request['query_string']);
        $this->server = $request;
        $pathInfo = pathinfo($request['request_uri']);
        if(in_array($pathInfo['extension'],array('css','js','png','git','jpeg','jpg'))){
            return $this->getStaticFile($request['request_uri']);
        } else {
            return $this->processRoute($request['request_uri']);
        }
    }

    /**
     * 处理静态文件
     * @param $filePath
     */
    public function getStaticFile($filePath)
    {
        $staticDir = dirname(__DIR__).'/'.ltrim($filePath, '/');
        echo $staticDir;
        if(file_exists($staticDir)){
            return file_get_contents($staticDir);
        } else {
            throw new \Exception('文件不存在',404);
        }
    }

    /**
     * 处理动态程序
     * @param $uri
     * @return mixed
     */
    public function processRoute($uri)
    {
        $className = 'Index';
        $methodName = 'Index';
        if(empty($uri) || trim($uri) == '/'){
            $controllerName = "\\Test\\Controller\\{$className}";
        } else {
            $uris = explode('/',$uri);
            $controllerName = "\\Test\\Controller\\{$uris[0]}";
            $methodName = $uris[1];
        }
        $controller = new $controllerName;
        return call_user_func_array(array($controller, $methodName));
    }

}