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

}
