<?php

class IndexController extends ControllerBase
{
    public function initialize()
    {
        //$this->tag->setTitle('Добро пожаловать!');
        parent::initialize();
    }

    public function indexAction()
    {
        $this->assets->addJs("public/js/bundle.js",true);
    }

}