<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class TenderAPIController extends Controller
{
    /**
     * Index action
     */
    public function indexAction()
    {
        if ($this->request->isPost() || true) {
            $today = date("Y-m-d");
            $query = $this->modelsManager->createQuery('SELECT * FROM tender, tasks WHERE tasks.status=\'Поиск\' AND tasks.taskId=tender.taskId AND tender.dateEnd>:today:');

            $auctions = $query->execute(
                [
                    'today' => "$today",
                ]
            );

            return json_encode($auctions);
        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}