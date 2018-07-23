<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class RegisterAPIController extends Controller
{
    /**
     * Регистрирует пользователя в системе
     *
     * @method POST
     *
     * @params (user) phone, email, password, (userinfo) firstname, lastname, male
     *
     * @return Response
     */
    public function indexAction()
    {
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
                        "status" => STATUS_ALREADY_EXISTS,
                        'errors' => ['Пользователь с таким телефоном/email-ом уже зарегистрирован']
                    ]
                );
                return $response;
            }

            $this->db->begin();

            $phoneObject = new Phones();
            $phoneObject->setPhone($phone);

            if ($phoneObject->save() == false) {
                $this->db->rollback();
                $errors = [];
                foreach ($phoneObject->getMessages() as $message) {
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

            $user = new Users();
            $user->setEmail($email);
            $user->setPhoneId($phoneObject->getPhoneId());
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

            }

            $userInfo = new Userinfo();
            $userInfo->setUserId($user->getUserId());
            $userInfo->setFirstname($this->request->getPost('firstname'));
            $userInfo->setLastname($this->request->getPost('lastname'));
            $userInfo->setMale($this->request->getPost('male'));

            if ($userInfo->save() == false) {
                $this->db->rollback();
                $errors = [];
                foreach ($userInfo->getMessages() as $message) {
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
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );

                return $response;
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}

