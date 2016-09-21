<?php
namespace Framework;

class Controller
{
    public $request;
    public $server;
    public $template;//模板处理类

    /**
     * 处理所有的请求分发
     * @param $request_uri
     */
    public function dealRequest($request,$response)
    {
        parse_str($request['query_string'],$output);
        $this->request = $output;
        $this->server = $request;
        $pathInfo = pathinfo($request['request_uri']);
        $mimeList = $this->getMimeTypeList();
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
            $this->processRoute($request['request_uri']);
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
            $className = $uris[0];
            $controllerName = "\\Controller\\{$className}";
            $methodName = "action".$uris[1];
        }
        $controller = new $controllerName;
        $controller->controllerName = $className;
        $controller->actionName = $methodName;
        $controller->request = $this->request;
        $controller->templatePath = strtolower($uris[1]).'/'.strtolower($uris[1]).'.php';
        $controller->viewPath = dirname(__DIR__).'/Views/';
        return call_user_func(array($controller, $methodName));
    }


    public function getMimeTypeList()
    {
        $mime_file = __DIR__.'/mime.types';
        if (!is_file($mime_file)) {
            return '';
        }
        $items = file($mime_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($items)) {
            return '';
        }
        $mimeList = array();
        foreach ($items as $content) {
            if (preg_match("/\s*(\S+)\s+(\S.+)/", $content, $match)) {
                $mime_type                      = $match[1];
                $workerman_file_extension_var   = $match[2];
                $workerman_file_extension_array = explode(' ', substr($workerman_file_extension_var, 0, -1));
                foreach ($workerman_file_extension_array as $workerman_file_extension) {
                    $mimeList[$workerman_file_extension] = $mime_type;
                }
            }
        }

        return $mimeList;
    }
}