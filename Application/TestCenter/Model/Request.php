<?php
namespace Model;

class Request extends Base
{
    public $request;
    public $server;

    /**
     * 处理所有的请求分发
     * @param $request_uri
     */
    public function dealRequest($request,$response)
    {
        $this->request = parse_str($request['query_string']);
        $this->server = $request;
        $pathInfo = pathinfo($request['request_uri']);

        $mimeList = Common::instance()->getMimeTypeList();
        if(in_array($pathInfo['extension'],array('css','js','png','git','jpeg','jpg','ico'))){
            //静态文件处理
            $staticDir = dirname(__DIR__).'/'.ltrim($request['request_uri'], '/');
            if(!file_exists($staticDir)){
                $response->status(404);
                $response->end('');
            }
            $stat = stat($staticDir);
            $modified_time = $stat ? date('D, d M Y H:i:s', $stat['mtime']) . ' GMT' : '';

            $file_size = filesize($staticDir);
            $mimeContent = $mimeList[$pathInfo['extension']];
            if($mimeContent){
                $response->header("Content-Type",$mimeContent);
            }
            $response->header('Connection','keep-alive');
            $response->header('Content-Length',$file_size);
            $response->status(200);
            $response->end(file_get_contents($staticDir));
        } else {
            $result = $this->processRoute($request['request_uri']);
            //$response->end($result);
        }
        return '';
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
        $className = "Index";
        $methodName = "actionIndex";
        if(empty($uri) || trim($uri) == '/'){
            $controllerName = "\\Controller\\{$className}";
        } else {
            $uris = explode('/',trim($uri,'/'));
            $uris[0] = ucfirst($uris[0]);
            $uris[1] = ucfirst($uris[1]);
            $controllerName = "\\Controller\\{$uris[0]}";
            $methodName = "action".$uris[1];
        }
        $controller = new $controllerName;
        return call_user_func(array($controller, $methodName));
    }

}