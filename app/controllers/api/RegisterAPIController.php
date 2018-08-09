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
     * @params login, password,
     *
     * @return string json array. Если все прошло успешно - [status, token, lifetime (время, после которого токен будет недействительным)],
     * иначе [status,errors => <массив сообщений об ошибках>]
     */
    public function indexAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();

            $phone = $this->request->getPost('login');
            $email = $this->request->getPost('login');
            $password = $this->request->getPost('password');
            $formatPhone = Phones::formatPhone($phone);
            $phoneObj = Phones::findFirstByPhone($formatPhone);

            $user = Users::findFirst(
                [
                    "(email = :email: OR phoneid = :phoneId:)",
                    "bind" => [
                        "email" => $email,
                        "phoneId" => $phoneObj ? $phoneObj->getPhoneId() : null
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

            $user = new Users();

            if (Phones::isValidPhone($formatPhone)) {
                $phoneObject = new Phones();
                $phoneObject->setPhone($formatPhone);

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
                $user->setPhoneId($phoneObject->getPhoneId());
            } else {
                $user->setEmail($email);
            }

            $user->setPassword($password);
            $user->setRole("Guests");
            $user->setIsSocial(false);
            $user->setActivated(false);

            if ($user->save() == false) {
                $this->db->rollback();
                $errors = [];
                foreach ($user->getMessages() as $message) {
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

            $this->SessionAPI->_registerSession($user);

            $token = Accesstokens::GenerateToken($user->getUserId(),($user->getEmail()!= null?$user->getEmail():$user->phones->getPhone()),
                $this->session->getId());

            $accToken = new Accesstokens();

            $accToken->setUserid($user->getUserId());
            $accToken->setToken($token);
            $accToken->setLifetime();

            if ($accToken->save() == false) {
                $this->db->rollback();
                $this->session->destroy();
                $errors = [];
                foreach ($accToken->getMessages() as $message) {
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
            //$this->db->commit();
            if ($user->getEmail() != null) {
                //Отправляем письмо.
                /*$this->mailer->sendView('emails/hello_world', [$user->getEmail()],
                    function($message) {
                    $message->to();
                    $message->subject('Test Email');
                });*/

                $viewPath = 'emails/hello_world';

                $message = $this->mailer->createMessageFromView($viewPath,[])
                    ->to($user->getEmail())
                    ->subject('Здарова');
                $message->send();
            }

            //Временно
           /* $_POST['firstname'] = 'Ехехе';
            $_POST['lastname'] = 'Эхеххов';
            $_POST['male'] = 1;
            $result = $this->confirmAction();*/

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    "token" => $token,
                    "lifetime" => $accToken->getLifetime()
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }


    /**
     * Активирует пользователя.
     *
     * @method POST
     *
     * @params (обязательные) firstname, lastname, male
     * @params (Необязательные) patronymic, birthday, about (много текста о себе),
     * @return string - json array Status
     */
    public function confirmAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $user = Users::findFirstByUserid($userId);

            if (!$user){
                $response->setJsonContent(
                    [
                        "status" => STATUS_UNRESOLVED_ERROR,
                        "errors" => ['Пользователь не создан']
                    ]
                );

                return $response;
            }

            if($user->getActivated()){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Пользователь уже активирован']
                    ]
                );
                return $response;
            }

            $this->db->begin();

            $userInfo = new Userinfo();
            $userInfo->setUserId($userId);
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

            $user->setRole('User');

            if ($user->update() == false) {
                $this->db->rollback();
                $errors = [];
                foreach ($user->getMessages() as $message) {
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
                    "status" => STATUS_OK,
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function testAction()
    {

    }
}

