<?php

class SessionController extends \Phalcon\Mvc\Controller
{

    private function _registerSession($user)
    {
    	
        $this->session->set(
            "auth",
            [
                "id"   => $user->getUserId(),
                "email" => $user->getEmail(),
                "role" => $user->getRole()
            ]
        );
    }

    /**
     * Это действие авторизует пользователя в приложении
     */
    public function indexAction(){
        $this->view->form = new AuthorizedForm;
    }

    public function startAction()
    {
        if ($this->request->isPost()) {
            // Получаем данные от пользователя
            $email    = $this->request->getPost("email");
            $password = $this->request->getPost("password");

            // Производим поиск в базе данных
            $user = Users::findFirst(
                [
                    "(email = :email: OR phone = :email:) AND password = :password:",
                    "bind" => [
                        "email"    => $email,
                        "password" => sha1($password),
                    ]
                ]
            );

            if ($user !== false) {
                $this->_registerSession($user);

                $this->flash->success(
                    "Welcome"
                );

                // Перенаправляем на контроллер 'invoices', если пользователь существует
                return $this->dispatcher->forward(
                    [
                        "controller" => "index",
                        "action"     => "index",
                    ]
                );
            }

            $this->flash->error(
                "Неверный email/пароль"
            );
        }

        // Снова выдаем форму авторизации
        return $this->dispatcher->forward(
            [
                "controller" => "session",
                "action"     => "index",
            ]
        );
    }

    public function endAction()
    {
        $this->session->remove('auth');
        $this->flash->success('Goodbye!');

        return $this->dispatcher->forward(
            [
                "controller" => "index",
                "action"     => "index",
            ]
        );
    }

}

