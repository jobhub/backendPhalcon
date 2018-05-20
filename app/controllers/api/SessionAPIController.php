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

                $response = new Response();
                $userinfo = Userinfo::findFirstByuserId($user->getUserId());
                if (!$userinfo) {

                    $response->setJsonContent(
                        [
                            "status" => ["status" => "FAIL"]
                        ]);

                    return $response;
                }
                /*$user = Users::findFirstByuserId($user->getUserId());
                if (!$user) {
                    $response->setJsonContent(
                        [
                            "status" => "FAIL"
                        ]);
                    return $response;
                }*/

                $user_min['userId'] = $user->getUserId();
                $user_min['email'] = $user->getEmail();
                $user_min['phone'] = $user->getPhone();

                $settings = Settings::findFirstByuserId($user->getUserId());
                if (!$settings) {

                    $response->setJsonContent(
                        [
                            "status" => ["status" => "FAIL"]
                        ]);

                    return $response;
                }
                $info['userinfo'] = $userinfo;
                $info['user'] = $user_min;
                $info['settings'] = $settings;



                $response->setJsonContent(
                    [

                        "status" => ["status" => "OK"],
                        "allForUser" => $info
                    ]
                );
            } else {
                $response->setJsonContent(
                    [
                        "status" => ["status" => "FAIL"]
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