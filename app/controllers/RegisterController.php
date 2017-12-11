<?php

class RegisterController extends ControllerBase
{

    public function initialize()
    {
        $this->tag->setTitle('Регистрация');
        parent::initialize();
    }

    public function indexAction()
    {
        $form = new RegisterForm;

        if ($this->request->isPost()) {

            if (!$form->isValid($_POST)) {
                $messages = $form->getMessages();

                foreach ($messages as $message) {
                    echo $message, '<br>';
                }
                $this->view->form = $form;
                return true;
            }

            $phone = $this->request->getPost('phone');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $repeatPassword = $this->request->getPost('repeatPassword');

            if ($password != $repeatPassword) {
                $this->flash->error('Пароли различны.');
                $this->view->form = $form;
                return true;
            }

            $user = Users::findFirst(
                [
                    "(email = :email: OR phone = :email:) AND password = :password:",
                    "bind" => [
                        "email"    => $email,
                        "password" => sha1($password),
                    ]
                ]
            );

            if($user!=false){
                $this->flash->error("Такой пользователь уже существует");
                return true;
            }

            $user = new Users();
            $user->setEMail($email);
            $user->setPhone($phone);
            $user->setPassword(sha1($password));
            $user->setRole("user");

            if ($user->save() == false) {

                foreach ($user->getMessages() as $message) {
                    $this->flash->error((string) $message);
                }
            } else {
                //Регистрация прошла успешно
                $userInfo = new Userinfo();
                $userInfo->setUserId($user->getUserId());
                $userInfo->setFirstname($this->request->getPost('firstname'));
                $userInfo->setLastname($this->request->getPost('lastname'));
                $userInfo->setMale($this->request->getPost('male'));
                $userInfo->setExecutor(0);

                if ($userInfo->save() == false) {

                    foreach ($userInfo->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }

                $setting = new Settings();
                $setting->setUserId($user->getUserId());

                if ($setting->save() == false) {

                    foreach ($setting->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }

                $this->tag->setDefault('email', '');
                $this->tag->setDefault('password', '');
                $this->flash->success('Спасибо за регистрацию.');


                return $this->dispatcher->forward(
                    [
                        "controller" => "index",
                        "action"     => "index",
                    ]
                );

            }
        }

        $this->view->form = $form;
    }

}

