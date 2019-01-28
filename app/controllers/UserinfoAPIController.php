<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http403Exception;
use App\Libs\SupportClass;
use App\Models\Users;
use App\Models\Userinfo;
use App\Models\PhonesUsers;
use App\Models\ImagesUsers;
use App\Models\News;
use App\Models\FavoriteUsers;
use App\Models\FavoriteCompanies;

use App\Services\ImageService;
use App\Services\UserInfoService;
use App\Services\UserService;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class UserinfoAPIController
 * Контроллер, который содержит методы для работы в общем с пользователями.
 * Реализует CRUD для пользователей без создания, добавление изображений с привязкой к пользователю.
 *
 * Методы без документации старые и неактуальные, но могут пригодиться в дальнейшем.
 */
class UserinfoAPIController extends AbstractController
{
    /*public function indexAction()
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
    }*/

    /*public function aboutAction()
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
    }*/

    /*public function handlerAction()
    {
        $response = new Response();
        include('../library/SimpleImage.php');
// Проверяем установлен ли массив файлов и массив с переданными данными
        if (isset($_FILES) && isset($_FILES['image'])) {
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

                $imageFormat = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $format = $imageFormat;
                if ($imageFormat == 'jpeg' || 'jpg')
                    $imageFormat = IMAGETYPE_JPEG;
                elseif ($imageFormat == 'png')
                    $imageFormat = IMAGETYPE_PNG;
                elseif ($imageFormat == 'gif')
                    $imageFormat = IMAGETYPE_GIF;
                else {
                    $response->setJsonContent(
                        [
                            "error" => ['Данный формат не поддерживается'],
                            "status" => "WRONG_DATA"
                        ]
                    );
                    return $response;
                }
                $filename = $_SERVER['DOCUMENT_ROOT'] . '/public/img/' . hash('crc32', $userinfo->getUserId()) . '.' . $format;
                //if()
                {
                    $image->save($filename, $imageFormat);
                    $imageFullName = str_replace('C:/OpenServer/domains/simpleMod2', '', $filename);
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
    }*/

    /**
     * Устанавливает одну из фотографий пользователя, как основную.
     * @access private
     * @method POST
     * @params image_id
     * @return Response - json array в формате Status.
     */
    public function setPhotoAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        try {
            $userId = $this->session->get('auth')['id'];

            $image = $this->imageService->getImageById($data['image_id'], ImageService::TYPE_USER);

            if ($image->getUserId() != $userId) {
                throw new ServiceException('Image not found', ImageService::ERROR_IMAGE_NOT_FOUND);
            }

            $userinfo = $this->userInfoService->getUserInfoById($userId);

            $this->userInfoService->changeUserInfo($userinfo, ['path_to_photo' => $image->getImagePath()]);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_UNABLE_CHANGE_USER_INFO:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_USER_INFO_NOT_FOUND:
                case ImageService::ERROR_IMAGE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Photo successfully changed');
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
    /*public function deleteUserAction($userId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $currentUserId = $auth['id'];
            $response = new Response();

            $user = Users::findFirstByUserid($userId);

            if (!$user || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($currentUserId, $userId, 0, 'deleteUser')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$user->delete()) {
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

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }*/

    /**
     * Восстанавливает пользователя
     *
     * @method POST
     *
     * @param userId
     *
     * @return string - json array - объект Status - результат операции
     */
    /*public function restoreUserAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $user = Users::findFirst(['userid = :userId:',
                'bind' => ['userId' => $this->request->getPost('userId')]], false);

            if (!$user || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId, $user->getUserId(), 0, 'restoreCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$user->restore()) {
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

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }*/

    /**
     * Возвращает публичные данные о пользователе.
     * Публичный метод.
     *
     * @method GET
     *
     * @param $user_id
     *
     * @return array [userinfo, [phones], [images], countNews, countSubscribers,
     *          countSubscriptions];
     */
    public function getUserInfoAction($user_id = null)
    {
        try {
            $currentUserId = $this->session->get('auth')['id'];

            $res_user_id = $user_id == null ? $currentUserId : $user_id;

            $userInfo = $this->userInfoService->getHandledUserInfoById($res_user_id, $currentUserId);

        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_USER_INFO_NOT_FOUND:
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('', $userInfo);
    }

    /**
     * Возвращает результат поиска пользователей.
     * Публичный метод.
     *
     * @method POST
     *
     * @params string query
     * @params age_min - минимальный возраст
     * @params age_max - максимальный возраст
     * @params male - пол
     * @params has_photo - фильтр, имеется ли у него фотография
     * @params page - номер страницы
     * @params page_size - размер страницы
     *
     * @return array [userinfo, [phones], [images], countNews, countSubscribers,
     *          countSubscriptions];
     */
    public function findUsersWithFiltersAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['query'] = $inputData->query;

        if($inputData->age_min!=null) {
            $data['age_min'] = $inputData->age_min;
        }
        $data['age_max'] = $inputData->age_max;

        $data['male'] = $inputData->male;

        $data['has_photo'] = $inputData->has_photo;
        /*if(!is_null($inputData->has_photo) && $inputData->has_photo != "false")
            $data['has_photo'] = true;
        elseif($inputData->has_photo == "false")
            $data['has_photo'] = false;*/

        if (is_null($inputData->page))
            $data['page'] = 1;
        else
            $data['page'] = $inputData->page;
        if (is_null($inputData->page_size))
            $data['page_size'] = Userinfo::DEFAULT_RESULT_PER_PAGE;
        else
            $data['page_size'] = $inputData->page_size;

        $users = Userinfo::findUsersByQueryWithFilters($data['query'],
            $data['page'], $data['page_size'],
            $data['age_min'], $data['age_max'],
            $data['male'], $data['has_photo']);

        return $users;
    }

    /**
     * Меняет данные текущего пользоваателя.
     * Приватный метод.
     *
     * @method PUT
     *
     * @params firstname
     * @params lastname
     * @params patronymic
     * @params birthday
     * @params male
     * @params status
     * @params about
     * @params address
     *
     * @return string - json array - результат операции
     */
    public function editUserInfoAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        if (!isset($data['path_to_photo']))
            unset($data['path_to_photo']);

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $userInfo = $this->userInfoService->getUserInfoById($userId);

            $userInfo = $this->userInfoService->changeUserInfo($userInfo, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_UNABLE_CHANGE_USER_INFO:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_USER_INFO_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('User\'s info successfully changed', $userInfo);
    }

    /**
     * Добавляет все прикрепленные изображения к пользователю. Но суммарно изображений не больше 10.
     *
     * @access private
     *
     * @method POST
     * @params image_texts - тексты к каждому изображению
     * @params (обязательно) изображения. Именование не важно.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addImagesAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data = /*$inputData->image_text*/
            $this->request->getPost('image_texts');
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $user = $this->userService->getUserById($userId);

            //$result = $this->imageService->addImagesToUser($this->request->getUploadedFiles(),$user);

            $this->db->begin();

            $ids = $this->imageService->createImagesToUser($this->request->getUploadedFiles(), $user, $data);

            $this->imageService->saveImagesToUser($this->request->getUploadedFiles(), $user, $ids);
        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ImageService::ERROR_UNABLE_CHANGE_IMAGE:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();

        return self::successResponse('Images were successfully saved');
    }

    /**
     * Добавляет все отправленные файлы изображений к пользователю. Общее количество
     * фотографий для пользователя на данный момент не более 10.
     * Доступ не проверяется.
     *
     * @param $userId
     * @return Response с json массивом типа Status
     */
    /*public function addImagesHandler($userId)
    {
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $user = Users::findFirstByUserid($userId);

            if (!$user) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор пользователя'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $images = ImagesUsers::findByUserid($userId);
            $countImages = count($images);

            if (($countImages + count($files)) > ImagesUsers::MAX_IMAGES) {
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для пользователя. 
                        Можно сохранить для одного пользователя не более чем ' . ImagesUsers::MAX_IMAGES . ' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $imagesIds = [];
            $this->db->begin();

            foreach ($files as $file) {

                $newimage = new ImagesUsers();
                $newimage->setUserId($userId);
                $newimage->setImagePath("magic_string");

                if (!$newimage->save()) {
                    $errors = [];
                    $this->db->rollback();
                    foreach ($newimage->getMessages() as $message) {
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

                $imagesIds[] = $newimage->getImageId();

                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $filename = ImageLoader::formFullImageName('users', $imageFormat, $userId, $newimage->getImageId());

                $newimage->setImagePath($filename);

                if (!$newimage->update()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadUserPhoto($file->getTempName(), $file->getName(),
                    $userId, $imagesIds[$i]);
                $i++;
                if ($result != ImageLoader::RESULT_ALL_OK || $result === null) {
                    if ($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED) {
                        $error = 'Формат одного из изображений не поддерживается';
                    } elseif ($result == ImageLoader::RESULT_ERROR_NOT_SAVED) {
                        $error = 'Не удалось сохранить изображение';
                    } else {
                        $error = 'Ошибка при загрузке изображения';
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => [$error]
                        ]
                    );
                    return $response;
                }
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        }
        $response->setJsonContent(
            [
                "status" => STATUS_OK
            ]
        );
        return $response;
    }*/

    /**
     * Удаляет картинку из списка фотографий пользователя
     *
     * @method DELETE
     *
     * @param $image_id integer id изображения
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageAction($image_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $image = $this->imageService->getImageById($image_id, ImageService::TYPE_USER);

            if ($image->getUserId() != $userId) {
                throw new ServiceException('Image not found', ImageService::ERROR_IMAGE_NOT_FOUND);
            }

            $this->imageService->deleteImage($image);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ImageService::ERROR_UNABLE_DELETE_IMAGE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ImageService::ERROR_IMAGE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Image was successfully deleted');
    }

    /*public function addUsersAction()
    {
        $userId = $this->getUserId();

        if($userId!=6) {
            throw new Http403Exception('Permission error');
        }
            $users = [];

            //юзеры
            $names = ['Родион', 'Всеслав', 'Никита', 'Бен', 'Ярополк', 'Абдула', 'Василиса'];
            $males = [1, 1, 1, 1, 1, 1, 0];
            $lastnames1 = ['Мраков', 'Стебль', 'Ладан', 'Маринов', 'Зрачков'];
            $lastnames0 = ['Мракова', 'Стебль', 'Ладан', 'Маринова', 'Зрачкова'];
            $emailsName = ['mrak', 'bigbranch', 'lastpoint', 'stronghunger', 'anyname',
                'littlemouse', 'stella', 'alldarkness'];

            $emailsPost = ['mail.ru', 'mail.com', 'yandex.ru', 'gmail.com', 'outlook.com'];
            $count = 10;

            $longhigh = 36.785256139080154;
            $longbottom = 37.73694681290828 - ($longhigh - 37.73694681290828);
            $latright = 55.23724689239517;
            $latleft = 55.748696337268484 - ($latright - 55.748696337268484);

            $diffLong = ($longhigh - $longbottom) / 1000;
            $diffLat = ($latright - $latleft) / 1000;

            for ($i = 0; $i < $count; $i++) {
                $pos = rand(0, count($names) - 1);
                $user['firstname'] = $names[$pos];
                $user['male'] = $males[$pos];
                if ($user['male'] == 0) {
                    $user['lastname'] = $lastnames0[rand(0, count($lastnames0) - 1)];
                } else {
                    $user['lastname'] = $lastnames1[rand(0, count($lastnames1) - 1)];
                }
                do {
                    $user['email'] = $emailsName[rand(0, count($emailsName) - 1)] . '@' .
                        $emailsPost[rand(0, count($emailsPost) - 1)];
                } while (Users::findFirstByEmail($user['email']));

                $user['password'] = '12345678';

                $user['latitude'] = $latleft + rand(0, 1000) * $diffLat;
                $user['longitude'] = $longbottom + rand(0, 1000) * $diffLong;
                $users[] = $user;
            }

            $this->db->begin();
            foreach ($users as $userArr) {
                $user = new Users();
                $user->setActivated(true);
                $user->setEmail($userArr['email']);
                $user->setPassword($userArr['password']);
                $user->setRole(ROLE_GUEST);

                if (!$user->save()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($user);
                }

                $userinfo = new Userinfo();
                $userinfo->setUserId($user->getUserId());
                $userinfo->setFirstname($userArr['firstname']);
                $userinfo->setLastname($userArr['lastname']);
                $userinfo->setMale($userArr['male']);

                if (!$userinfo->save()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($userinfo);
                }

                $userlocation = new UserLocation();
                $userlocation->setUserId($userinfo->getUserId());
                $userlocation->setLastTime('2019-09-08 16:00:30+00');
                $userlocation->setLatitude($userArr['latitude']);
                $userlocation->setLongitude($userArr['longitude']);

                if (!$userlocation->save()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($userlocation);
                }
            }
            $this->db->commit();
            $response->setJsonContent(['status' => STATUS_OK]);

            return $response;

    }*/
}