<?php
namespace Mongo;

class HostSwitch
{

    private static $instanceObj;
    private static $config;

    private function __construct(){}

    public static function config($config)
    {
        if(empty($config)){
            self::$config = (array) new \Config\Mongo();
        } else {
            self::$config = $config;
        }
    }

    public static function instance()
    {
        if(!self::$instanceObj){
            self::$instanceObj = new self;
        }
        return self::$instanceObj;
    }

    public function getConfig()
    {
        return self::$config;
    }

}
