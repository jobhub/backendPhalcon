<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class RegisterAPIController extends Controller
{
    public function indexAction()
    {
        // Формируем ответ
        if ($this->request->isPost()) {
            $response = new Response();

            $phone = $this->request->getPost('phone');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $user = Users::findFirst(
                [
                    "(email = :email: OR phone = :phone:)",
                    "bind" => [
                        "email" => $email,
                        "phone" => $phone,
                    ]
                ]
            );

            if ($user != false) {
                $response->setJsonContent(
                    [
                        "status" => "ALREADY_EXISTS"
                    ]
                );
                return $response;
            }

            $this->db->begin();

            $user = new Users();
            $user->setEMail($email);
            $user->setPhone($phone);
            $user->setPassword($password);
            $user->setRole("User");

            if ($user->save() == false) {
                $this->db->rollback();

                $errors = [];

                foreach ($user->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }

                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;

            } else {
                //Регистрация прошла успешно
                $userInfo = new Userinfo();
                $userInfo->setUserId($user->getUserId());
                $userInfo->setFirstname($this->request->getPost('firstname'));
                $userInfo->setLastname($this->request->getPost('lastname'));
                $userInfo->setMale($this->request->getPost('male'));
                $userInfo->setExecutor(0);

                if ($userInfo->save() == false) {

                    $this->db->rollback();
                    $errors = [];

                    foreach ($userInfo->getMessages() as $message) {
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

                $setting = new Settings();
                $setting->setUserId($user->getUserId());


                if ($setting->save() == false) {

                    $this->db->rollback();
                    $errors = [];

                    foreach ($setting->getMessages() as $message) {
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
            }
            $this->db->commit();
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;
        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}

