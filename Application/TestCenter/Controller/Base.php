<?php

namespace Controller;
use Framework\Template;


/**
 * Base.
 */
class Base
{
    public $template;//模板处理类
    public $controllerName;//控制器名称
    public $actionName; //方法名称
    public $request; //方法名称
    public $useLayout;
    public $templatePath;
    public $viewPath;

    public function  __construct()
    {
        $this->useLayout = true;
        $this->template = new \Framework\Template();
        $this->template->useLayout = true;
        //$this->template->use_sub_dirs = true;
    }

    //protected static $Instances;

    /**
     * 获得对象的方法，请使用该方法获得对象 基础model的单例模式.
     *
     * @todo 这个方法子类没用且有一个同名方法，会报一个"strict standards"
     * @return static
     */
    /*
    public static function instance()
    {
        $className = get_called_class();
        return self::InstanceInternal($className);
    }
    */

    /**
     * 获取内部对象的方法.
     *
     * @param string $className 类名.
     *
     * @return mixed
     */
    /*
    protected static function InstanceInternal($className)
    {
        if (!isset( self::$Instances[$className] )) {
            self::$Instances[$className] = new $className();
        }
        return self::$Instances [$className];
    }
    */


    public function assign($key,$val)
    {
        $this->template->assign($key,$val);
    }

    /**
     * @param $template
     */
    public function display($template='')
    {
        $this->template->viewPath = $this->viewPath;
        $this->template->controllerName = $this->controllerName;
        $this->template->actionName = $this->actionName;
        $this->template->useLayout = $this->useLayout;
        if(!empty($template)){
            $this->template->templatePath = $template;
        } else {
            $this->template->templatePath = $this->templatePath;
        }

        $this->template->viewPath = $this->viewPath;
        $this->template->layoutPath = '/layout/layout.php';
        $this->template->display('index/index.php');
    }

}
