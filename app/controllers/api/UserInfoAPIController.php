<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class UserinfoAPIController extends Controller
{
    public function indexAction()
    {
        $auth = $this->session->get("auth");
        if ($this->request->isGet()) {
            $response = new Response();
            $userinfo = Userinfo::findFirstByUserid($auth['id']);
            if (!$userinfo) {

                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);

                return $response;
            }
            $user = Users::findFirstByuserid($auth['id']);
            if (!$user) {
                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);
                return $response;
            }
            $user_min['email'] = $user->getEmail();
            $user_min['phone'] = $user->getPhone();

            $settings = Settings::findFirstByuserid($auth['id']);
            if (!$settings) {

                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);

                return $response;
            }
            $info['Userinfo'] = $userinfo;
            $info['user'] = $user_min;
            $info['settings'] = $settings;

            return json_encode($info);
        } else if ($this->request->isPost()) {
            $response = new Response();

            $userId = $auth['id'];
            $userinfo = Userinfo::findFirstByuserid($userId);

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
                $errors = [];
                foreach ($userinfo->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => STATUS_WRONG
                    ]);

                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => STATUS_OK
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
            $userinfo = Userinfo::findFirstByuserid($userId);

            if (!$userinfo) {
                $errors[] = "Пользователь не авторизован";
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => STATUS_WRONG
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
            $settings = Settings::findFirstByuserid($userId);

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

    public function handlerAction()
    {
        $response = new Response();
        include('../library/SimpleImage.php');
// Проверяем установлен ли массив файлов и массив с переданными данными
        if(isset($_FILES) && isset($_FILES['image'])) {
            // echo $_FILES;
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $userinfo = Userinfo::findFirstByuserid($userId);
            if ($userinfo) {
                $userinfo->setUserid($auth['id']);


                if (($_FILES['image']['size'] > 5242880)) {
                    $response->setJsonContent(
                        [
                            "error" => ['Размер файла слишком большой'],
                            "status" => "WRONG_DATA"
                        ]
                    );
                    return $response;
                }
                $image = new SimpleImage();
                $image->load($_FILES['image']['tmp_name']);
                $image->resizeToWidth(200);

                $imageFormat = pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION );
                $format=$imageFormat;
                if($imageFormat=='jpeg'||'jpg')
                    $imageFormat=IMAGETYPE_JPEG;
                elseif ($imageFormat=='png')
                    $imageFormat=IMAGETYPE_PNG;
                elseif ($imageFormat=='gif')
                    $imageFormat=IMAGETYPE_GIF;
                else {
                    $response->setJsonContent(
                        [
                            "error" => ['Данный формат не поддерживается'],
                            "status" => "WRONG_DATA"
                        ]
                    );
                    return $response;
                }
                $filename=$_SERVER['DOCUMENT_ROOT'].'/public/img/'. hash('crc32',$userinfo->getUserId()).'.'.$format;
                //if()
                {
                    $image->save($filename,$imageFormat);
                    $imageFullName=str_replace('C:/OpenServer/domains/simpleMod2','',$filename);
                    $userinfo->setPathToPhoto($imageFullName);
                    $userinfo->save();



                    //return $userinfo->getPathToPhoto();
                    $response->setJsonContent(
                        [
                            'pathToPhoto' => $userinfo->getPathToPhoto(),
                            "status" => "OK"
                        ]
                    );
                    return $response;
                }

            }
            $response->setJsonContent(
                [
                    "status" => "WRONG_DATA"
                ]
            );
            return $response;
        }
        $response->setJsonContent(
            [
                "status" => "WRONG_DATA"
            ]
        );
        return $response;
    }

    public function setPhotoAction()
    {
        if($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            if ($this->request->hasFiles()) {
                $auth = $this->session->get('auth');
                $userId = $auth['id'];
                $userinfo = Userinfo::findFirstByUserid($userId);

                $file = $this->request->getUploadedFiles();
                $file = $file[0];
                if ($userinfo) {
                    $format = pathinfo($file->getName(), PATHINFO_EXTENSION);

                    $filename = ImageLoader::formFullImageName('users', $format, $userId, 0);
                    $userinfo->setPathToPhoto($filename);

                    if (!$userinfo->update()) {
                        $errors = [];
                        foreach ($userinfo->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "errors" => $errors,
                                "status" => STATUS_WRONG
                            ]);

                        return $response;
                    }

                    ImageLoader::loadUserPhoto($file->getTempName(), $file->getName(), $userId);

                    $response->setJsonContent(
                        [
                            "status" => STATUS_OK
                        ]
                    );
                    return $response;
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Пользователя не существует (или он не активирован)']
                    ]
                );
                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => ['Файл не отправлен']
                ]
            );
            return $response;
        } else{
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Удаляет пользователя
     *
     * @method DELETE
     *
     * @param $userId
     *
     * @return string - json array - объект Status - результат операции
     */
    public function deleteUserAction($userId){
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $currentUserId = $auth['id'];
            $response = new Response();

            $user = Users::findFirstByUserid($userId);

            if(!$user || !SubjectsWithNotDeleted::checkUserHavePermission($currentUserId,$userId,0,'deleteUser')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if(!$user->delete()){
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

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                ]
            );
            return $response;

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
    /**
     * Восстанавливает пользователя
     *
     * @method POST
     *
     * @param userId
     *
     * @return string - json array - объект Status - результат операции
     */
    public function restoreUserAction(){
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $user = Users::findFirst(['userid = :userId:',
                'bind' => ['userId' => $this->request->getPost('userId')]], false);

            if(!$user || !SubjectsWithNotDeleted::checkUserHavePermission($userId,$user->getUserId(),0,'restoreCompany')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if(!$user->restore()){
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

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                ]
            );
            return $response;

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}