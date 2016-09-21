<?php

namespace Controller;

class Index extends Base
{

    public function actionIndex()
    {
        /*
        echo "<pre>";
        var_dump($this->controllerName);
        var_dump($this->request);
        var_dump($this->templatePath);
        */


        $this->display();
    }

    public function actionTest()
    {
        /*
        echo "<pre>";
        var_dump($this->controllerName);
        var_dump($this->request);
        var_dump($this->templatePath);
        */

        $this->display();
    }

}