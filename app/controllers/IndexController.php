<?php

class IndexController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Добро пожаловать!');
        parent::initialize();
    }

    public function indexAction()
    {
        $this->assets->addJs("http://api-maps.yandex.ru/2.1/?lang=ru_RU",false);
        $this->assets->addJs("/public/js/mapTender.js",true);
    }

}

