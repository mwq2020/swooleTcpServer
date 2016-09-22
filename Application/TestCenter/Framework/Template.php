<?php
namespace Framework;

class Template
{
    public $controllerName;//控制器名称
    public $ActionName; //方法名称
    public $request; //方法名称
    public $useLayout; //是否使用layout
    public $templatePath; //模板的目录
    public $viewPath; //模板根目录
    public $layoutPath; //layout地址

    protected $current_template_file;
    /**
     * static variables  assigned global tpl vars
     */
    public $tpl_vars = array();

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
        $filePath = $this->viewPath . $tpl . '.php';
        if (!file_exists($filePath)) {
            $filePath = $this->viewPath . $tpl;
        }
        $realpath = $filePath;
        if (file_exists($filePath) && is_readable($filePath)) {
            $this->current_template_file = $filePath;
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
        $tpl = !empty($tpl) ? $tpl : $this->templatePath;
        echo $this->fetch($tpl);
    }

    public function fetch($tpl, $withLayout = true)
    {
        $result = $this->template($tpl, $realpath);
        if (!$result) {
            return $this->error('Template "'.$realpath.'" not found!');
        }
        if (defined('PHPUNIT_MODE') && PHPUNIT_MODE) {
            return '';
        }
        extract($this->tpl_vars);
        ob_start();
        include $result;
        $content = ob_get_clean();
        $this->assign('content',$content);

        if ($this->useLayout){
            $layout = empty($this->layoutPath) ? '/layout/layout.php' : $this->layoutPath;
            $this->useLayout = false;
            $content = $this->fetch($layout);
        }
        echo $content;
    }

    public function error($code)
    {
        throw new \Exception($code);
    }

    public function includes($template)
    {
        $this->fetch($template, false);
    }

}