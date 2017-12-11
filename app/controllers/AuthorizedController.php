<?php

class AuthorizedController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $this->view->form = new AuthorizedForm;
    }

}

