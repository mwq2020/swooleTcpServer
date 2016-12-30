<?php
namespace Controller;

class Index extends \Framework\CController
{

    //入口
    public function actionIndex()
    {
        $this->assign('test_key','test_name');
        return $this->display('index');
    }


}