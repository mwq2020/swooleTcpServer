<?php
namespace Config;

class Mongo
{

    public $DEBUG=true;
    public $DEBUG_LEVEL=1;

    //默认的mongo配置
    public $default = array(
        'uri'      => '127.0.0.1',
        'port'     => 27017,
        'user'     => 'root',
        'password' => 'root',
    );
    
}