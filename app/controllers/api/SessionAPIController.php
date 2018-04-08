<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class SessionAPIController extends Controller
{
    private function _registerSession($user)
    {
        $this->session->set(
            "auth",
            [
                "id" => $user->getUserId(),
                "email" => $user->getEmail(),
                "role" => $user->getRole()
            ]
        );
    }

    public function indexAction()
    {
        if ($this->request->isPost()) {
            $email = $this->request->getPost("email");
            $password = $this->request->getPost("password");

            // Производим поиск в базе данных
            $user = Users::findFirst(
                [
                    "(email = :email: OR phone = :email:) AND password = :password:",
                    "bind" => [
                        "email" => $email,
                        "password" => sha1($password),
                    ]
                ]
            );
            // Формируем ответ
            $response = new Response();

            if ($user !== false) {
                $this->_registerSession($user);

                $response->setJsonContent(
                    [
                        "status" => "OK"
                    ]
                );
            } else {
                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);
            }

            return $response;
        }
        else{
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}