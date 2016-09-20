<?php

namespace Controller;

class Index extends Base
{
    public function actionIndex()
    {
        include __DIR__.'/../Views/header.tpl.php';
        //include __DIR__.'/../Views/'.strtolower(__CLASS__).'/'.strtolower(__FUNCTION__).'.php';
        include __DIR__.'/../Views/footer.tpl.php';
    }

}