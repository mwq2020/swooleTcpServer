<?php
namespace Test\Models;

class Common extends Base
{

    /**
     * 处理所有的请求分发
     * @param $request_uri
     */
    public function dealRequest($request)
    {
        echo "ffff<br>";
        echo "hhhhh<br>";
        echo "asdfasfaf<br>";
        echo "hhhhhh<br>";
        return "ggg<br>";
    }

    /**
     * 处理静态文件
     * @param $filePath
     */
    public function getStaticFile($filePath)
    {

    }

}