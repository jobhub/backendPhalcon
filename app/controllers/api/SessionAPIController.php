<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use ULogin\Auth;

class SessionAPIController extends Controller
{
    public function _registerSession($user)
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

    /**
     * Авторизует пользователя в системе
     *
     * @method POST
     * @params login, password
     * @return Response
     */
    public function indexAction()
    {
        if ($this->request->isPost()) {
            $login = $this->request->getPost("login");
            $password = $this->request->getPost("password");

            // Производим поиск в базе данных

            $phone = Phones::findFirstByPhone(Phones::formatPhone($login));
            if ($phone) {
                $user = Users::findFirst(
                    [
                        "phoneid = :phoneId: AND password = :password: AND issocial=false",
                        "bind" => [
                            "phoneId" => $phone->getPhoneId(),
                            "password" => sha1($password),
                        ]
                    ]
                );
            } else {
                $user = Users::findFirst(
                    [
                        "email = :login: AND password = :password: AND issocial=false",
                        "bind" => [
                            "login" => $login,
                            "password" => sha1($password),
                        ]
                    ]
                );
            }

            // Формируем ответ
            $response = new Response();

            if ($user) {
                $this->_registerSession($user);

                $response = new Response();
                $userinfo = Userinfo::findFirstByUserid($user->getUserId());

                if (!$userinfo) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_UNRESOLVED_ERROR,
                            "errors" => ['Нет userinfo при существующем user']
                        ]);

                    return $response;
                }

                $user_min['userId'] = $user->getUserId();
                $user_min['email'] = $user->getEmail();
                $user_min['phone'] = $user->phones->getPhone();

                $settings = Settings::findFirstByUserid($user->getUserId());
                if (!$settings) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_UNRESOLVED_ERROR,
                            "error" => ['Нет settings при существующем user']
                        ]);

                    return $response;
                }
                $info['userinfo'] = $userinfo;
                $info['user'] = $user_min;
                $info['settings'] = $settings;


                $response->setJsonContent(
                    [

                        "status" => STATUS_OK,
                        "allForUser" => $info
                    ]
                );
            } else {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        'errors' => ['Неверные логин или пароль']
                    ]);
            }

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Авторизация через соц. сеть
     */
    public function authWithSocialAction()
    {
        if ($this->request->isGet()) {
            $ulogin = new Auth(array(
                'fields' => 'first_name,last_name,email,phone,sex',
                'url' => '/sessionAPI/authWithSocial',
                'optional' => 'pdate,photo_big,city,country',
                'type' => 'panel',
            ));
            return $ulogin->getForm();
        } else if ($this->request->isPost()) {
            $ulogin = new Auth(array(
                'fields' => 'first_name,last_name,email,phone,sex',
                'url' => '/sessionAPI/authWithSocial',
                'optional' => 'pdate,photo_big,city,country',
                'type' => 'panel',
            ));
            if ($ulogin->isAuthorised()) {
                $response = new Response();
                $ulogin->logout();
                $userSocial = Userssocial::findByIdentity($ulogin->getUser()['network'], $ulogin->getUser()['identity']);

                if (!$userSocial) {

                    //Регистрируем
                    $phone = $ulogin->getUser()['phone'];
                    $email = $ulogin->getUser()['email'];

                    $phoneObj = Phones::findFirstByPhone(Phones::formatPhone($phone));

                    $user = Users::findFirst(
                        [
                            "(email = :email: OR phoneid = :phoneId:)",
                            "bind" => [
                                "email" => $email,
                                "phoneId" =>  $phoneObj?$phoneObj->getPhoneId():null
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

                    if($phone!=null) {
                        //Добавление телефона, если есть
                        $phoneObject = new Phones();
                        $phoneObject->setPhone($phone);

                        if ($phoneObject->save()) {
                            $user->setPhoneId($phoneObject->getPhoneId());
                        }
                    }

                    $user->setEmail($email);
                    $user->setIsSocial(true);
                    $user->setRole("User");

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

                    $userInfo = new Userinfo();
                    $userInfo->setUserId($user->getUserId());
                    $userInfo->setFirstname($ulogin->getUser()['first_name']);
                    $userInfo->setLastname($ulogin->getUser()['last_name']);
                    $userInfo->setMale(($ulogin->getUser()['sex']-1) >= 0 ? $ulogin->getUser()['sex']-1:1);
                    if(isset($ulogin->getUser()['country']) && isset($ulogin->getUser()['city']))
                        $userInfo->setAddress($ulogin->getUser()['country'] .' '. $ulogin->getUser()['city']);

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

                    $userSocial = new Userssocial();
                    $userSocial->setUserId($user->getUserId());
                    $userSocial->setNetwork($ulogin->getUser()['network']);
                    $userSocial->setIdentity($ulogin->getUser()['identity']);
                    $userSocial->setProfile($ulogin->getUser()['profile']);

                    if ($userSocial->save() == false) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($userSocial->getMessages() as $message) {
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

                    $this->SessionAPI->_registerSession($user);

                    $response->setJsonContent(
                        [
                            "status" => STATUS_OK
                        ]
                    );
                    return $response;
                }

                //Авторизуем
                $this->SessionAPI->_registerSession($userSocial->users);

                $response->setJsonContent([
                    'status' => STATUS_OK
                ]);
                return $response;
            }else {
                $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

                throw $exception;
            }
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}