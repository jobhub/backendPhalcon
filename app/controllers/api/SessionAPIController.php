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

            $phone = Phones::findFirstByPhone($login);
            if($phone){
                $user = Users::findFirst(
                    [
                        "phoneId = :phoneId: AND password = :password:",
                        "bind" => [
                            "phoneId" => $phone->getPhoneId(),
                            "password" => sha1($password),
                        ]
                    ]
                );
            } else {
                $user = Users::findFirst(
                    [
                        "email = :login: AND password = :password:",
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
                $userinfo = Userinfo::findFirstByuserId($user->getUserId());

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

                $settings = Settings::findFirstByuserId($user->getUserId());
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
        }
        else{
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}