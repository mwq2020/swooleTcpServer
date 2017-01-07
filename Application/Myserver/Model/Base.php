<?php

namespace Model;


/**
 * Base.
 */
class Base
{
    protected static $Instances;

    /**
     * 获得对象的方法，请使用该方法获得对象 基础model的单例模式.
     *
     * @todo 这个方法子类没用且有一个同名方法，会报一个"strict standards"
     * @return static
     */
    public static function instance()
    {
        $className = get_called_class();
        return self::InstanceInternal($className);
    }

    /**
     * 获取内部对象的方法.
     *
     * @param string $className 类名.
     *
     * @return mixed
     */
    protected static function InstanceInternal($className)
    {
        if (!isset( self::$Instances[$className] )) {
            self::$Instances[$className] = new $className();
        }
        return self::$Instances [$className];
    }


    public function create_uuid($prefix = "")
    {    //可以指定前缀
        $str = md5(uniqid(mt_rand(), true));
        $uuid  = substr($str,0,8) . '-';
        $uuid .= substr($str,8,4) . '-';
        $uuid .= substr($str,12,4) . '-';
        $uuid .= substr($str,16,4) . '-';
        $uuid .= substr($str,20,12);
        return $prefix . $uuid;
    }

    public function create_guid($namespace = '')
    {
        static $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['LOCAL_ADDR'];
        $data .= $_SERVER['LOCAL_PORT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = '{' .
            substr($hash,  0,  8) .
            '-' .
            substr($hash,  8,  4) .
            '-' .
            substr($hash, 12,  4) .
            '-' .
            substr($hash, 16,  4) .
            '-' .
            substr($hash, 20, 12) .
            '}';
        return $guid;
    }

}
