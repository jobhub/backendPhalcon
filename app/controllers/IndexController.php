<?php
use Phalcon\Mvc\Controller;
class IndexController extends Controller
{
    public function indexAction()
    {
        $this->assets->addJs("public/js/bundle.js",true);
    }

}