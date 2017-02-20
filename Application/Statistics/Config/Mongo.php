<?php
/**
 * mongo 配置文件
 * @author xmc
 */

namespace Config;
class Mongo
{
    public static function getConfig() {
        $config = array(
            'host' => '127.0.0.1',
            'port' => '27017',
        );
        return $config;
    }

    public $default = ['url' => 'mongodb://127.0.0.1:27017','dbname' => 'default'];
    public $statistics = ['url' => 'mongodb://127.0.0.1:27017','dbname' => 'Statistics'];
    public $statisticsLog = ['url' => 'mongodb://127.0.0.1:27017','dbname' => 'StatisticsLog'];

}
