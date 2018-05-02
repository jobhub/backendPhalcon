<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class CategoriesAPIController extends Controller
{
    /**
     * Index action
     */
    public function indexAction()
    {
        if ($this->request->isPost() || $this->request->isGet()) {
            $categories = Categories::find();
            return json_encode($categories);
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
