<?php
/**
 * 框架的控制器基类
 */
namespace Framework;
class Controller
{

    /**
     * 框架的执行入口
     */
    public function Run()
    {

    }

    public $request;
    public $template;//模板处理类


    public $get;
    public $post;
    public $server;

    /**
     * 处理所有的请求分发
     * @param $request_uri
     */
    public function dealRequest($request,$response)
    {
        $this->get = $request->get;
        $this->post = $request->post;
        $this->server = $request->server;

        //parse_str($request->server['query_string'],$output);
        //$this->request = $output;
        //$this->server = $request;

        try{
            $mimeList = $this->getMimeTypeList();
            $pathInfo = pathinfo($request->server['request_uri']);
            if(in_array($pathInfo['extension'],array('css','js','png','git','jpeg','jpg','ico'))){
                //静态文件处理
                $staticDir = dirname(__DIR__).'/'.ltrim($request->server['request_uri'], '/');
                if(!file_exists($staticDir)){
                    $response->status(404);
                    return $response->end('');
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
                return $response->end(file_get_contents($staticDir));
            } else {
                ob_start();
                //print_r($request->server);
                $this->processRoute($request->server['request_uri']);
            }
            $result = ob_get_clean();
        } catch(\Exception $e){
            $result = $e->getMessage();
            $response->status(500);
        }
        return $response->end($result);
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
        $methodName = "Index";
        if(empty($uri) || trim($uri) == '/'){
            $controllerName = "\\Controller\\{$className}";
        } else {
            $uris = explode('/',trim($uri,'/'));
            $uris[0] = ucfirst($uris[0]);
            $uris[1] = ucfirst($uris[1]);
            $className = $uris[0];
            $controllerName = "\\Controller\\{$className}";
            $methodName = $uris[1];
        }
        $controller = new $controllerName;
        $controller->controllerName = $className;
        $controller->actionName = $methodName;
        //$controller->request = $this->request;
        $controller->templatePath = strtolower($className).'/'.strtolower($methodName).'.php';
        $controller->viewPath = dirname(__DIR__).'/Views/';

        $controller->get = $this->get;
        $controller->post = $this->post;
        $controller->request = array_merge((array)$this->get,(array)$this->post);
        $methodName = "action".$methodName;
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