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
                    "(email = :email: OR phone = :phone:)",
                    "bind" => [
                        "email"    => $email,
                        "phone"    => $phone,
                    ]
                ]
            );

            if($user!=false){
                $this->flash->error("Такой пользователь уже существует");
                $this->view->form = $form;
                return true;
            }

            $this->db->begin();

            $user = new Users();
            $user->setEMail($email);
            $user->setPhone($phone);
            $user->setPassword($password);
            $user->setRole("User");

            if ($user->save() == false) {
                $this->db->rollback();

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

                    $this->db->rollback();
                    foreach ($userInfo->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }

                $setting = new Settings();
                $setting->setUserId($user->getUserId());

                if ($setting->save() == false) {

                    $this->db->rollback();
                    foreach ($setting->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }

                $this->tag->setDefault('email', '');
                $this->tag->setDefault('password', '');
                $this->flash->success('Спасибо за регистрацию.');


                $this->db->commit();
                return $this->dispatcher->forward(
                    [
                        "controller" => "session",
                        "action"     => "start",
                    ]
                );

            }
        }

        $this->view->form = $form;
    }

}

