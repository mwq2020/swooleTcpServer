<?php
/**
 * 框架的模板基类
 */

namespace Framework;
class Template
{

    public $controllerName;  //控制器名称
    public $actionName;      //方法名称
    protected $current_template_file;  //当前的模板文件

    public $request;         //方法名称
    public $useLayout;       //是否使用layout
    public $templatePath;    //模板的目录
    public $viewPath;        //模板根目录
    public $layoutPath;      //layout地址

    public $tpl_vars = array(); //存储模板变量对象

    /**
     * 模板构造函数
     * @param unknown_type $config
     */
    public function __construct()
    {

    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
    }

    //获取当前的模板文件名
    public function currentTemplateFile()
    {
        return $this->current_template_file;
    }

    /**
     *
     * @param 键名 $name
     * @return Ambigous <boolean, multitype:>
     */
    public function __get($name)
    {
        return isset($this->tpl_vars[$name]) ? $this->tpl_vars[$name] : false;

    }

    /**
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function __set($name, $value)
    {
        $this->tpl_vars[$name] = $value;

    }

    /**
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function assign($tpl_var, $value=null)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $_key => $_val) {
                if ($_key != '') {
                    $this->$_key = $_val;
                }
            }
        } else {
            if ($tpl_var != '') {
                $this->$tpl_var = $value;
            }
        }
    }

    public function getVar($name)
    {
        return $this->$name;
    }


    public function template($tpl, &$realpath='')
    {
        if(empty($this->viewPath)){
            $this->viewPath = dirname(__DIR__).'/Views/';
        }
        $filePath = $this->viewPath ."/". $tpl . '.php';
        //echo $filePath;
        if (!file_exists($filePath)) {
            $filePath = $this->viewPath . $tpl;
        }
        $realpath = $filePath;
        if (file_exists($filePath) && is_readable($filePath)) {
            return $filePath;
        }
        return false;
    }

    /**
     * 模板渲染
     * @param String $tpl
     * @return html
     */
    public function display($tpl = '')
    {
        echo $this->fetch($tpl);
    }

    public function fetch($tpl, $withLayout = true)
    {
        if(empty($this->templatePath)){
            $this->templatePath = dirname(__DIR__).'/Views/';
        }
        if($withLayout){
            $tpl = $this->actionName."/".$tpl.".php";
        }
        $result = $this->template($tpl, $realpath);
        if (!$result) {
            return $this->error('Template file 【"'.$realpath.'"】 not found!');
        }

        extract($this->tpl_vars);
        ob_start();
        include $result;
        $content = ob_get_clean();
        $this->assign('content',$content);

        if ($this->useLayout){
            $layout = empty($this->layoutPath) ? 'layout/layout.php' : trim($this->layoutPath,"/");
            $this->useLayout = false;
            $content = $this->fetch($layout,false);
        }
        echo $content;
    }

    //抛出异常的方法
    public function error($code)
    {
        throw new \Exception($code);
    }

    //包含文件到模板中
    public function includes($template)
    {
        $this->fetch($template, false);
    }

}