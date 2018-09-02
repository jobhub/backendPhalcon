<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class UserLocationAPIController extends Controller
{
    /**
     * Устанавливает текущее местоположение текущего пользователя.
     * Приватный метод.
     *
     * @method POST
     * @params latitude;
     * @params longitude;
     * @return string - json array результат операции.
     */
    public function setLocationAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get("auth");
            $response = new Response();
            $userId = $auth['id'];
            $userlocation = UserLocation::findFirstByUserid($userId);
            if (!$userlocation) {

                $userlocation = new UserLocation();
                $userlocation->setLatitude($this->request->getPost('latitude'));
                $userlocation->setLongitude($this->request->getPost('longitude'));
                $userlocation->setUserId($userId);
                $userlocation->setLastTime(time());

                if (!$userlocation->save()) {
                    $errors = [];
                    foreach ($userlocation->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }

            } else{
                $userlocation->setLatitude($this->request->getPost('latitude'));
                $userlocation->setLongitude($this->request->getPost('longitude'));
                $userlocation->setLastTime(time());

                if (!$userlocation->update()) {
                    $errors = [];
                    foreach ($userlocation->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }
            }

            $response->setJsonContent([
                "status" => STATUS_OK
            ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}