<?php
/**
 * Created by PhpStorm.
 * User: mwq
 * Date: 16/12/28
 * Time: 15:11
 */
namespace Framework;

class CController
{

    public $template;//模板处理类
    public $controllerName;//控制器名称
    public $actionName; //方法名称【带action的控制器名称】
    public $actionShortName;//方法名称【不带action的控制器名称】

    public $useLayout;
    public $templatePath;

    public $get;
    public $post;
    public $request;

    public $called_class;
    //public $called_method;

    public function  __construct()
    {
        $this->useLayout = true;
        $this->template = new Template();
        $this->template->useLayout = true;
        $classArr = explode('\\',get_called_class());
        $this->called_class = strtolower($classArr[1]);
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
    public function display($templateName='')
    {
        if(empty($templateName)){
            throw new \Exception('模板名称不能为空');
        }

        $this->template->useLayout = $this->useLayout;
        $this->template->actionName = $this->actionShortName;
        $this->template->controllerName = $this->called_class;
        $this->template->current_template_file = $templateName;
        $this->template->layoutPath = '/layout/layout.php';
        $this->template->display($templateName);
    }
}