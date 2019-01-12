<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Контроллер для работы с уведомлениями.
 * Содержит методы для получения уведомлений
 */
class NotificationsAPIController extends Controller
{
    /**
     * Возвращает "общие" уведомления
     *
     * @method GET
     *
     * @return string - json array с категориями
     */
    public function getCommonNotificationsAction()
    {
        if ($this->request->isGet()) {

            $response = new Response();
            $response->setJsonContent([
                'status' => STATUS_OK,
                'notifications' => ""
            ]);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
