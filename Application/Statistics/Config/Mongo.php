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

    public $default = ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'default'];
    public $statistics = ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'Statistics'];
    public $statisticsLog = ['uri' => 'mongodb://127.0.0.1:27017','dbname' => 'StatisticsLog'];

}
