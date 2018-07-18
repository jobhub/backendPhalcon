<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

class RequestsAPIController extends Controller
{
    /**
     * Добавляет запрос на получение услуги
     *
     * @method POST
     *
     * @params serviceId, description, dateEnd.
     *
     * @return Response с json массивом в формате Status
     */
    public function addRequestAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = new Requests();

            $request->setServiceId($this->request->getPost("serviceId"));
            $request->setUserId($userId);
            $request->setDescription($this->request->getPost("description"));
            $request->setDateEnd(date('Y-m-d H:i:s',strtotime($this->request->getPost("dateEnd"))));

            if (!$request->save()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Удаляет заявку
     *
     * @method DELETE
     *
     * @param $requestId
     * @return Response с json массивом в формате Status
     */
    public function deleteRequestAction($requestId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestId($requestId);

            if (!request || ($request->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$request->delete()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }

                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Редактирует заявку
     *
     * @method PUT
     *
     * @params requestId, description, dateEnd
     * @return Response с json массивом в формате Status
     */
    public function editRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestId($this->request->getPut('requestId'));

            if (!$request || ($request->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $request->setDescription($this->request->getPut('description'));
            $request->setDateEnd(date('Y-m-d H:i:s',strtotime($this->request->getPut('dateEnd'))));

            if (!$request->save()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }

                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
