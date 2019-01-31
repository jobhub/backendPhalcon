<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 22.01.2019
 * Time: 14:07
 */

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http403Exception;
use App\Libs\SupportClass;
use App\Models\ImagesModel;
use App\Models\Accounts;

use App\Services\ImageService;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http404Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class ImageController
 * Контроллер для изображений. Реализует общий CRUD для всех изображений (без редактирования).
 */
class ImageController extends AbstractController
{
    /**
     * Возвращает изображения для указанного объекта.
     * Тип может быть:
     *      user
     *      news
     *      review
     *      service
     *      company (пока еще не реализовано)
     *
     * @access private
     *
     * @method GET
     * @param $type string
     * @param $object_id int
     * @param $page
     * @param $page_size
     * @param $account_id
     * @params (обязательно) изображения. Именование не важно.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function getImagesAction($type, $object_id, $account_id = null, $page = 1,
                                    $page_size = ImagesModel::DEFAULT_RESULT_PER_PAGE)
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

        try {
            $images = $this->imageService->getImages($object_id, $type, $page, $page_size);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    $exception = new Http404Exception(
                        _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                        new \Exception('URI not found: ' .
                            $this->request->getMethod() . ' ' . $this->request->getURI())
                    );
                    throw $exception;
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return $images;
    }

    /**
     * Добавляет все прикрепленные изображения к указанному объекту.
     *
     * @access private
     *
     * @method POST
     *
     * @param $type ;
     *
     * @params object_id
     *
     * @params image_text в случае изображения пользователя
     *
     * @params (обязательно) изображения.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addImagesAction($type)
    {
        try {
            $this->db->begin();
            /*$data['object_id'] = $this->request->getPost('object_id');
            $data['image_text'] = $this->request->getPost('image_text');*/
            $inputData = $this->request->getJsonRawBody();
            $data['object_id'] = $inputData->object_id;
            $data['image_text'] = $inputData->image_text;

            $userId = self::getUserId();

            $object = $this->imageService->checkPermissionToObject($type, $data['object_id'], $userId, 'addImage');

            if (!$object) {
                throw new Http403Exception('Permission error');
            }


            $ids = $this->imageService->createImagesToObject($this->request->getUploadedFiles(), $object, $type, $data);
            //$ids = $this->imageService->createImagesToNews($this->request->getUploadedFiles(),$news);

            $this->imageService->saveImagesToObject($this->request->getUploadedFiles(), $object, $ids, $type);

            $addedImages = [];
            foreach ($ids as $image_id){
                $addedImages[] = $this->imageService->getModelForType($type)::handleImage($this->imageService->getImageById($image_id,$type)->toArray());
            }

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
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    $exception = new Http404Exception(
                        _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                        new \Exception('URI not found: ' .
                            $this->request->getMethod() . ' ' . $this->request->getURI())
                    );
                    throw $exception;
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Image was successfully added to object', $addedImages);
    }

    /**
     * Удаляет картинку из списка изображений
     * @access private
     *
     * @method DELETE
     *
     * @param $type
     * @params (посылается в теле запроса) $image_id id изображения или же массив id-шников изображений
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageByIdAction($type)
    {
        try {
            $this->db->begin();
            $inputData = $this->request->getJsonRawBody();
            $data['image_id'] = $inputData->image_id;

            $userId = self::getUserId();

            if (!is_array($data['image_id'])) {
                $data['image_id'] = [$data['image_id']];
            }

            foreach ($data['image_id'] as $image_id) {

                $image = $this->imageService->getImageById($image_id, $type);

                $object = $this->imageService->checkPermissionToObject($type, $image->getObjectId()
                    , $userId, 'deleteImage');

                if (!$object) {
                    throw new Http403Exception('Permission error');
                }

                $this->imageService->deleteImage($image);
            }

            $this->db->commit();
        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ImageService::ERROR_UNABLE_DELETE_IMAGE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ImageService::ERROR_IMAGE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Image(s) was successfully deleted');
    }
}