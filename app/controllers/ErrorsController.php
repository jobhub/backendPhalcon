<?php

/**
 * ErrorsController
 *
 * Manage errors
 */
class ErrorsController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Ooops');
        parent::initialize();
    }

    public function show404Action()
    {
        //$this->view->pick("errors/show404");
    }

    public function show401Action()
    {

    }

    public function show500Action()
    {

    }
}
