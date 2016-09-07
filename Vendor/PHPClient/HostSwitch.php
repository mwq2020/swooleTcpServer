<?php

class HostSwitch
{
    public static $instance;
    public static $config;

    //开启单例模式
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Rpc;
        }
        return self::$instance;
    }

    public function config()
    {

    }

}