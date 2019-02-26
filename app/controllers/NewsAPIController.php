<?php

namespace App\Controllers;

use App\Libs\SupportClass;
use App\Models\ImagesTemp;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

use App\Models\ImagesUsers;
use App\Models\News;
use App\Models\Accounts;

use App\Services\ImageService;
use App\Services\NewsService;
use App\Services\AccountService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Контроллер для работы с новостями.
 * Реализует CRUD для новостей, позволяет просматривать новости тех, на кого подписан текущий пользователь.
 * Ну и методы для прикрепления изображений к новости.
 */
class NewsAPIController extends AbstractController
{
    /**
     * Возвращает новости для ленты текущего пользователя или указанного аккаунта (компании)
     * @access private
     *
     * @param $account_id
     * @param $page
     * @param $page_size
     * @method GET
     *
     * @return string - json array с новостями (или их отсутствием)
     */
    public function getNewsAction($account_id = null, $page = 1, $page_size = News::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        $account = $this->accountService->checkPermissionOrGetDefaultAccount($userId,$account_id);

        self::setAccountId($account->getId());

        return News::findNewsForCurrentAccount($account, $page, $page_size);
    }

    /**
     * Возвращает все новости юзера и новости тех, на кого он подписан.
     * Пока прростая логика с выводом только лишь новостей (без других объектов типа заказов, услуг)
     *
     * @access private
     *
     * @method GET
     *
     * @param $account_id
     * @param $page
     * @param $page_size
     * @return string - json array с новостями (или их отсутствием)
     */
    public function getAllNewsAction($account_id = null, $page = 1, $page_size = News::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if ($account_id != null && SupportClass::checkInteger($account_id)) {
            if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                throw new Http403Exception('Permission error');
            }

            $account = Accounts::findFirstById($account_id);

        } else {
            $account = Accounts::findForUserDefaultAccount($userId);
        }

        self::setAccountId($account->getId());

        return News::findAllNewsForCurrentUser($account, $page, $page_size);
    }

    /**
     * Возвращает новости текущего пользователя/указанной компании пользователя.
     *
     * @method GET
     *
     * @param $page
     * @param $page_size
     * @param $account_id
     *
     * @return string - json array объектов news или Status, если ошибка
     */
    public function getOwnNewsAction($account_id = null, $page = 1, $page_size = News::DEFAULT_RESULT_PER_PAGE)
    {
        try {
            $userId = self::getUserId();

            if ($account_id != null && SupportClass::checkInteger($account_id)) {
                if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                    throw new Http403Exception('Permission error');
                }

            } else {
                $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
            }

            $this->session->set('accountId', $account_id);
            $account = $this->accountService->getAccountById($account_id);

            if($account->getCompanyId() != null){
                return News::findNewsByCompany($account->getCompanyId(), $page, $page_size);
            } else {
                return News::findNewsByUser($userId, $page, $page_size);
            }

        }catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception(_($e->getMessage()), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }


    /**
     * Создает новость компании или пользователя.
     * Если прикрепить изображения, они будут добавлены к новости.
     *
     * @access private
     *
     * @method POST
     *
     * @params int account_id (если не передать, то от имени аккаунта юзера по умолчанию)
     * @params string news_text
     * @params string title
     * @params string publish_date
     * @params string news_type
     *
     * @params array temp_images - массив с id временных изображений, которые должны быть добавлены в новость
     *
     * @params файлы изображений.
     * @return string - json array объекта Status
     */
    public function addNewsAction()
    {
        //$inputData = $this->request->getJsonRawBody();
        $inputData = json_decode(json_encode($this->request->getPost()));
        $data['news_text'] = $inputData->news_text;
        $data['title'] = $inputData->title;
        $data['account_id'] = $inputData->account_id;
        $data['news_type'] = $inputData->news_type;

        $data['temp_images'] = $inputData->temp_images;

        if (!is_null($inputData->publish_date))
            $data['publish_date'] = date('Y-m-d H:i:sO', strtotime($inputData->publish_date));

        $this->db->begin();
        try {
            $userId = self::getUserId();

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addNews')) {
                throw new Http403Exception('Permission error');
            }

            if (!is_null($data['publish_date']) && (time() - strtotime($data['publish_date']) > 3600)) {

                if (strtotime($data['publish_date']) == 0)
                    $errors['publish_date'] = 'date of publication have invalid format';
                else
                    $errors['publish_date'] = 'date of publication too early';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception('Invalid some parameters', self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $news = $this->newsService->createNews($data);
            $news = $this->newsService->getNewsById($news->getNewsId());

            if ($this->request->hasFiles()) {
                $files = $this->request->getUploadedFiles();
                $ids = $this->imageService->createImagesToNews($files, $news);
                $this->imageService->saveImagesToNews($files, $news, $ids);
            }

            $new_news_text = $news->getNewsText();
            $tempImages = [];
            $filenames = [];
            if ($data['temp_images'] != null) {
                foreach ($data['temp_images'] as $temp_image_id) {
                    $tempImage = ImagesTemp::findFirstByImageId($temp_image_id);

                    if ($tempImage->getObjectId() != $news->getAccountId())
                        throw new Http403Exception('Permission error');

                    $oldPath = $tempImage->getImagePath();

                    $newPath = $this->imageService->transferTempImageToNewsObject($tempImage, $news->getNewsId());

                    $filenames[] = $newPath;
                    $new_news_text = str_replace($oldPath, $newPath, $new_news_text);
                    $tempImages[] = $tempImage;
                }

                $new_data['news_text'] = $new_news_text;
                $this->newsService->changeNews($news, $new_data);

                $i = 0;
                foreach ($tempImages as $tempImage) {
                    $newPath = $this->imageService->transferTempImageToNewsFile($tempImage, $news->getNewsId(), $filenames[$i]);
                    $i++;
                }
            }

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ImageService::ERROR_UNABLE_CHANGE_IMAGE:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                case NewsService::ERROR_UNABLE_CREATE_NEWS:
                case NewsService::ERROR_UNABLE_CHANGE_NEWS:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE_FROM_TEMP:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();

        return self::successResponse('News was successfully created',
            ['news_id' => SupportClass::getCertainColumnsFromArray($news->toArray(), News::publicColumns)]);
    }

    /**
     * Удаляет указанную новость
     *
     * @method DELETE
     *
     * @param $news_id
     *
     * @return string - json array объекта Status
     */
    public function deleteNewsAction($news_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $news = $this->newsService->getNewsById($news_id);

            if (!Accounts::checkUserHavePermission($userId, $news->getAccountId(), 'deleteNews')) {
                throw new Http403Exception('Permission error');
            }

            $this->newsService->deleteNews($news);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case NewsService::ERROR_UNABLE_DELETE_NEWS:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case NewsService::ERROR_NEWS_NOT_FOUND:
                    throw new Http422Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('News was successfully deleted');
    }

    /**
     * Редактирует новость.
     * Дата устанавливается текущая (на сервере).
     *
     * @method PUT
     *
     * @params int news_id, string news_text, title
     *
     * @return string - json array объекта Status
     */
    public function editNewsAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['news_text'] = $inputData->news_text;
        $data['title'] = $inputData->title;
        $data['news_id'] = $inputData->news_id;

        try {

            //validation
            if (empty(trim($data['news_id']))) {
                $errors['news_id'] = 'Missing required parameter "news_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $news = $this->newsService->getNewsById($data['news_id']);

            if (!Accounts::checkUserHavePermission($userId, $news->getAccountId(), 'editNews')) {
                throw new Http403Exception('Permission error');
            }

            unset($data['news_id']);

            $this->newsService->changeNews($news, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case NewsService::ERROR_UNABLE_CHANGE_NEWS:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case NewsService::ERROR_NEWS_NOT_FOUND:
                    throw new Http400Exception(_($e->getMessage()), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('News was successfully changed');
    }
    /**
     * Возвращает новости указанного объекта
     *
     * @method GET
     * @param $page
     * @param $page_size
     * @param $id
     * @param $is_company (Можно не указывать, значение по умолчанию 0)
     * @param $account_id - аккаунт от имени которого совершаются действия.
     *
     * @return string - json array объектов news или Status, если ошибка
     */
    public function getSubjectsNewsAction($id, $is_company = false, $account_id = null,
                                          $page = 1, $page_size = News::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if ($account_id != null && is_integer(intval($account_id))) {
            if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                throw new Http403Exception('Permission error');
            }
        } else {
            $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
        }

        self::setAccountId($account_id);

        if ($is_company && strtolower($is_company) != "false")
            return $news = News::findNewsByCompany($id, $page, $page_size);
        else
            return $news = News::findNewsByUser($id, $page, $page_size);
    }

    /**
     * Добавляет все прикрепленные изображения к новости. Но суммарно изображений не больше некоторого количества.
     *
     * @access private
     *
     * @method POST
     *
     * @params news_id
     * @params (обязательно) изображения. Именование не важно.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addImagesAction()
    {
        try {
            /*$sender = $this->request->getJsonRawBody();

            $data['news_id'] = $sender->news_id;*/

            $data['object_id'] = $this->request->getPost('object_id');

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $news = $this->newsService->getNewsById($data['object_id']);

            if (!Accounts::checkUserHavePermission($userId, $news->getAccountId(), 'editNews')) {
                throw new Http403Exception('Permission error');
            }

            $this->db->begin();

            $ids = $this->imageService->createImagesToNews($this->request->getUploadedFiles(), $news);

            $this->imageService->saveImagesToNews($this->request->getUploadedFiles(), $news, $ids);

            $this->db->commit();
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
                case NewsService::ERROR_NEWS_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Image was successfully added to news');
    }

    /**
     * Добавляет все отправленные файлы изображений к новости. Общее количество
     * фотографий для пользователя на данный момент не более некоторого количества.
     * Доступ не проверяется.
     *
     * @param $newId
     * @return Response с json массивом типа Status
     */
    /*public function addImagesHandler($newId)
    {
        include(APP_PATH . '/library/SimpleImage.php');
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $new = News::findFirstByNewsid($newId);

            if (!$new) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор новости'],
                        "status" => STATUS_WRONG,
                    ]
                );
                return $response;
            }

            $images = ImagesNews::findByNewsid($newId);
            $countImages = count($images);

            if (($countImages + count($files)) > ImagesNews::MAX_IMAGES) {
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для новости. 
                        Можно сохранить для одной новости не более чем ' . ImagesUsers::MAX_IMAGES . ' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $imagesIds = [];
            $this->db->begin();

            foreach ($files as $file) {

                $newimage = new ImagesNews();
                $newimage->setNewsId($newId);
                $newimage->setImagePath('magic_string');

                if (!$newimage->save()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }

                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);
                $imageFILEName = $file->getKey();

                if ($imageFILEName == "title") {
                    $imagesIds[] = $imageFILEName;
                    $filename = ImageLoader::formFullImageName('news', $imageFormat, $newId, $imageFILEName);
                } else {
                    $imagesIds[] = $newimage->getImageId();
                    $filename = ImageLoader::formFullImageName('news', $imageFormat, $newId, $newimage->getImageId());
                }
                $newimage->setImagePath($filename);

                if (!$newimage->update()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadNewImage($file->getTempName(), $file->getName(),
                    $newId, $imagesIds[$i]);
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
     * Удаляет картинку из списка изображений новости
     * @access private
     *
     * @method DELETE
     *
     * @param $image - путь к изображению
     *
     * @return string - json array в формате Status - результат операции
     */
    /*public function deleteImageAction($images, $subpath, $newid, $imageName)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $image = ImagesNews::findFirstByImagepath(
                ImageLoader::formFullImagePathFromImageName($subpath, $newid, $imageName));

            if (!$image) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный путь к изображению'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $news = News::findFirstByNewsid($image->getNewsId());

            if (!$news || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId, $news->getSubjectId(),
                    $news->getSubjectType(), 'editNews')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$image->delete()) {
                $errors = [];
                foreach ($image->getMessages() as $message) {
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

            //$result = ImageLoader::delete($image->getImagePath());
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
    }*/

    /**
     * Удаляет картинку из списка изображений новости
     * @access private
     *
     * @method DELETE
     *
     * @param $newsId id новости
     * @param $imageName название изображения с расширением
     *
     * @return string - json array в формате Status - результат операции
     */
    /*public function deleteImageByNameAction($newsId, $imageName)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $news = News::findFirstByNewsid($newsId);

            if (!$news || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId, $news->getSubjectId(),
                    $news->getSubjectType(), 'editNews')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $image = ImagesNews::findFirstByImagepath(
                ImageLoader::formFullImagePathFromImageName('news', $newsId, $imageName));

            if (!$image) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверное название изображения'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$image->delete()) {
                $errors = [];
                foreach ($image->getMessages() as $message) {
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

            //$result = ImageLoader::delete($image->getImagePath());
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
    }*/

    /**
     * Удаляет картинку из списка изображений новости
     * @access private
     *
     * @method DELETE
     *
     * @param $image_id id изображения
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageByIdAction($image_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $image = $this->imageService->getImageById($image_id, ImageService::TYPE_NEWS);

            if (!Accounts::checkUserHavePermission($userId, $image->news->getAccountId(), 'editNews')) {
                throw new Http403Exception('Permission error');
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
}
