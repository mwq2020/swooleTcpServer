<?php
/**
 * Autoloader.
 *
 * @author Su Chao<suchaoabc@163.com>
 */

namespace Bootstrap;

/**
 * 实现类的自动加载
 */
class Autoloader{
    protected static $sysRoot = array();
    protected static $instance;
    protected $classPrefixes = array();
    protected $logRoot = '/tmp/autoload.log'; //自动加载日志目录
    protected function __construct()
    {
        static::$sysRoot = array(
            //默认的项目根目录
            //__DIR__.'/../../',
            // Vendor目录
            __DIR__.'/../'
        );
    }

    /**
     *
     * @return self
     */
    public static function instance()
    {
        if(!static::$instance)
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * 添加根目录. 默将使用Autoloader目录所在的上级目录为根目录。
     *
     * @param string $path
     * @return self
     */
    public function addRoot($path)
    {
        static $called;
        if(!$called)
        {
            // 取消默认的项目根目录
            unset(static::$sysRoot[0]);
            $called = true;
        }
        static::$sysRoot[] = $path;
        return $this;
    }

    /**
     * 按命名空间自动加载相应的类.
     *
     * @param string $name 命名空间及类名
     * @return boolean
     */
    public function loadByNamespace($name)
    {
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR ,$name);

        foreach(static::$sysRoot as $k => $root)
        {
            $classFile = $root.$classPath.'.php';
            //file_put_contents($this->logRoot, "\r\n".$classFile."\r\n", FILE_APPEND);
            if(is_file($classFile))
            {
                require_once($classFile);
                if(class_exists($name, false)) {
                    return true;
                }
            }
            else
            {
                /*
                // 对thrift provider文件的支持
                $interfaceStr = substr($name, strlen($name)-2);
                if(strpos($name, 'Provider\\') === 0 && $interfaceStr === 'If')
                {
                    substr_replace($classFile, '', strlen($classFile), 6, 2);
                    if(is_file($classFile))
                    {
                        require_once($classFile);
                        return true;
                    }
                }
                */
            }
        }

        return false;
    }

    /**
     *
     * @return \Bootstrap\Autoloader
     */
    public function init()
    {
        spl_autoload_register(array($this, 'loadByNamespace'));
        return $this;
    }
}