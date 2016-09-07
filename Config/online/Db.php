<?php
namespace Config;

class Db{

    public $DEBUG=true;

    public $DEBUG_LEVEL=1;

    /**
     * Configs of database.
     * @var array
     */
    public $read = array(
        'test' => array(
            'dsn'      => 'mysql:host=127.0.0.1;port=3306;dbname=test',
            'user'     => 'root',
            'password' => 'root',
            'confirm_link' => true,//required to set to TRUE in daemons.
            'options'  => array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                \PDO::ATTR_TIMEOUT=>3
            )
        ),
        'python' => array(
            'dsn'      => 'mysql:host=127.0.0.1;port=3306;dbname=python',
            'user'     => 'root',
            'password' => 'root',
            'confirm_link' => true,//required to set to TRUE in daemons.
            'options'  => array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                \PDO::ATTR_TIMEOUT=>3
            )
        ),
    );

    public $write = array(
        'test' => array(
            'dsn'      => 'mysql:host=127.0.0.1;port=3306;dbname=test',
            'user'     => 'root',
            'password' => 'root',
            'confirm_link' => true,//required to set to TRUE in daemons.
            'options'  => array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                \PDO::ATTR_TIMEOUT=>3
            )
        ),
        'python' => array(
            'dsn'      => 'mysql:host=127.0.0.1;port=3306;dbname=python',
            'user'     => 'root',
            'password' => 'root',
            'confirm_link' => true,//required to set to TRUE in daemons.
            'options'  => array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                \PDO::ATTR_TIMEOUT=>3
            )
        ),
    );

}