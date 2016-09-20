<?php
namespace Model;

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