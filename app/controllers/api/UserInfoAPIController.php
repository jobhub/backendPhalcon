<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

class UserinfoAPIController extends Controller
{
    public function indexAction()
    {
        $auth = $this->session->get("auth");
        if ($this->request->isGet()) {
            $response = new Response();
            $userinfo = Userinfo::findFirstByuserId($auth['id']);
            if (!$userinfo) {

                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);

                return $response;
            }
            $user = Users::findFirstByuserId($auth['id']);
            if (!$user) {
                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);
                return $response;
            }
            $user_min['email'] = $user->getEmail();
            $user_min['phone'] = $user->getPhone();

            $settings = Settings::findFirstByuserId($auth['id']);
            if (!$settings) {

                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);

                return $response;
            }
            $info['userinfo'] = $userinfo;
            $info['user'] = $user_min;
            $info['settings'] = $settings;

            return json_encode($info);
        } else if ($this->request->isPost()) {
            $response = new Response();

            $userId = $auth['id'];
            $userinfo = Userinfo::findFirstByuserId($userId);

            if (!$userinfo) {
                $errors[] = "Пользователь не авторизован";
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "FAIL"
                    ]);

                return $response;
            }

            $userinfo->setFirstname($this->request->getPost("firstname"));
            $userinfo->setPatronymic($this->request->getPost("patronymic"));
            $userinfo->setLastname($this->request->getPost("lastname"));
            $userinfo->setAddress($this->request->getPost("address"));
            $userinfo->setBirthday(date('Y-m-d H:m', strtotime($this->request->getPost("birthday"))));
            $userinfo->setMale($this->request->getPost("male"));

            if (!$userinfo->save()) {

                foreach ($userinfo->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "WRONG_DATA"
                    ]);

                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function aboutAction()
    {
        $auth = $this->session->get("auth");

        if ($this->request->isPost()) {
            $response = new Response();

            $userId = $auth['id'];
            $userinfo = Userinfo::findFirstByuserId($userId);

            if (!$userinfo) {
                $errors[] = "Пользователь не авторизован";
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "FAIL"
                    ]);

                return $response;
            }

            $userinfo->setAbout($this->request->getPost("about"));

            if (!$userinfo->save()) {

                foreach ($userinfo->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "WRONG_DATA"
                    ]);

                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function settingsAction()
    {
        $auth = $this->session->get("auth");
        if ($this->request->isPost()) {

            $response = new Response();

            $userId = $auth['id'];
            $settings = Settings::findFirstByuserId($userId);

            if (!$settings) {
                $errors[] = "Пользователь не авторизован";
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "FAIL"
                    ]);

                return $response;
            }
            if (isset($_POST["notificationEmail"]))
                $settings->setNotificationEmail($this->request->getPost("notificationEmail"));
            if (isset($_POST["notificationSms"]))
                $settings->setNotificationSms($this->request->getPost("notificationSms"));
            if (isset($_POST["notificationPush"]))
                $settings->setNotificationPush($this->request->getPost("notificationPush"));

            /*if($settings->getNotificationEmail())
                $settings->setNotificationEmail(1);
            else
                $settings->setNotificationEmail(0);

            if($settings->getNotificationSms())
                $settings->setNotificationSms(1);
            else
                $settings->setNotificationSms(0);

            if($settings->getNotificationPush())
                $settings->setNotificationPush(1);
            else
                $settings->setNotificationPush(0);*/


            if (!$settings->save()) {

                foreach ($settings->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "WRONG_DATA"
                    ]);

                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}