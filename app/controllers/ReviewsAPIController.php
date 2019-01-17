<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 02.05.2018
 * Time: 11:45
 */

namespace App\Controllers;

use App\Services\RequestService;
use App\Services\ReviewService;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Paginator\Adapter\NativeArray as Paginator;

use App\Models\ImagesUsers;
use App\Models\News;
use App\Models\Accounts;
use App\Models\Binders;
use App\Models\Reviews;

use App\Services\ImageService;
use App\Services\NewsService;
use App\Services\AccountService;
use App\Services\TaskService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class ReviewsAPIController
 * Контроллер для работы с отзывами.
 * Реализует CRUD для отывов, методы для добавления изображений к отзыву.
 */
class ReviewsAPIController extends AbstractController
{
    /**
     * Добавляет отзыв.
     *
     * @method POST
     *
     * @params int binder_id, int binder_type, bool executor, int rating, string review_text
     *
     * @return Response - Status
     */
    public function addReviewAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['binder_id'] = $inputData->binder_id;
        $data['binder_type'] = $inputData->binder_type;
        $data['rating'] = $inputData->rating;
        $data['review_text'] = $inputData->review_text;

        try {
            $userId = self::getUserId();

            //validation
            if (empty(trim($data['binder_id']))) {
                $errors['binder_id'] = 'Missing required parameter "binder_id"';
            }

            if (empty(trim($data['binder_type'])) && $data['binder_type'] != 0) {
                $errors['binder_type'] = 'Missing required parameter "binder_type"';
            }

            /*if (empty(trim($data['executor']))) {
                $errors['executor'] = 'Missing required parameter "executor"';
            }*/

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            //проверки
            if ($data['binder_type'] == 'task' || $data['binder_type'] == 1) {
                $binder = $this->taskService->getTaskById($data['binder_id']);
            } elseif ($data['binder_type'] == 'request' || $data['binder_type'] == 2)
                $binder = $this->requestService->getRequestById($data['binder_id']);
            else {
                $errors['errors'] = true;
                $errors['binder_type'] = 'Invalid parameter "binder_type". Must be "request" (or 1) or "task" (or 0).';
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            //getting executor
            if(Accounts::checkUserRelatesWithAccount($userId,$binder->getAccountId())){
                $data['executor'] = false;
            } else{
                $data['executor'] = true;
            }

            if (!Binders::checkUserHavePermission($userId, $data['binder_id'],
                $data['binder_type'], $data['executor'], 'addReview')) {
                throw new Http403Exception('Permission error');
            }

            if (!($binder->getStatus() == STATUS_CANCELED ||
                $binder->getStatus() == STATUS_NOT_EXECUTED ||
                $binder->getStatus() == STATUS_REJECTED_BY_SYSTEM ||
                $binder->getStatus() == STATUS_PAID_EXECUTOR ||
                $binder->getStatus() == STATUS_PAID_BY_SECURE_TRANSACTION)) {

                $errors['errors'] = true;
                $errors['binder_id'] = 'Can\'t create review on current stage of executing.';
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            if(Reviews::reviewAlreadyExists($data['binder_id'],$data['binder_type'],$data['executor'])){
                throw new Http400Exception('Review already exists');
            }

            $data['review_date'] = date('Y-m-d H:i:s');

            $review = $this->reviewService->createReview($data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ReviewService::ERROR_UNABLE_CREATE_REVIEW:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }catch (ServiceException $e) {
            switch ($e->getCode()) {
                case TaskService::ERROR_TASK_NOT_FOUND:
                case RequestService::ERROR_REQUEST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Review was successfully created', ['review' => $review->toArray()]);
    }

    /**
     * Редактирует отзыв.
     *
     * @method PUT
     *
     * @params int rating, review_id
     * @param (Необязатальные) review_text.
     *
     * @return Response - Status
     */
    public function editReviewAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['review_id'] = $inputData->review_id;
        $data['rating'] = $inputData->rating;
        $data['review_text'] = $inputData->review_text;

        try {
            //validation
            if (empty(trim($data['review_id']))) {
                $errors['review_id'] = 'Missing required parameter "review_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $userId = self::getUserId();

            $review = $this->reviewService->getReviewById($data['review_id']);

            if (!Binders::checkUserHavePermission($userId, $review->getBinderId(),
                $review->getBinderType(),$review->getExecutor(), 'editReview')) {
                throw new Http403Exception('Permission error');
            }

            unset($data['review_id']);

            $this->reviewService->changeReview($review, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ReviewService::ERROR_UNABLE_CHANGE_REVIEW:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ReviewService::ERROR_REVIEW_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Review was successfully changed');
    }

    /**
     * Удаляет отзыв.
     *
     * @method DELETE
     *
     * @param $review_id
     *
     * @return Response - Status
     */
    public function deleteReviewAction($review_id)
    {
        try {
            $userId = self::getUserId();

            $review = $this->reviewService->getReviewById($review_id);

            if (!Binders::checkUserHavePermission($userId, $review->getBinderId(),
                $review->getBinderType(),$review->getExecutor(), 'deleteReview')) {
                throw new Http403Exception('Permission error');
            }

            $this->reviewService->deleteReview($review);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ReviewService::ERROR_UNABLE_DELETE_REVIEW:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ReviewService::ERROR_REVIEW_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Review was successfully deleted');
    }

    /**
     * Возвращает отзывы об указанном субъекте, будь то пользователь или компания.
     *
     * @method GET
     *
     * @param $id - id субъекта
     * @param $is_company - тип субъекта
     *
     * @return string - json array [status,[reviews]]
     */
    public function getReviewsForSubjectAction($id, $is_company = false)
    {
        if ($is_company && strtolower($is_company) != "false")
            return Reviews::findReviewsByCompany($id);
        else
            return Reviews::findReviewsByUser($id);
    }

    /**
     * Возвращает отзывы, связанные с указанной услугой.
     *
     * @method GET
     *
     * @param $service_id - id услуги
     * @param $num_page - номер страницы
     * @param $width_page - размер страницы
     *
     * @return string - json array [status,reviews => [review,{userinfo or company}]]
     */
    public function getReviewsForServiceAction($service_id, $num_page, $width_page)
    {
        $reviews = Reviews::findReviewsForService($service_id);

        $paginator = new Paginator([
            'data' => $reviews,
            'limit' => $width_page,
            'page' => $num_page
        ]);


        return $paginator->getPaginate()->items;
    }

    /**
     * Добавляет все прикрепленные изображения к отзыву. Но суммарно изображений для отзыва не больше 3.
     *
     * @access private
     *
     * @method POST
     *
     * @params (обязательно) review_id
     * @params (обязательно) изображения. Именование не важно.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addImagesAction()
    {
        $this->db->begin();
        try {
            $data['review_id'] = $this->request->getPost('review_id');

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $review = $this->reviewService->getReviewById($data['review_id']);

            if (!Binders::checkUserHavePermission($userId, $review->getBinderId(), $review->getBinderType(),$review->getExecutor(), 'editReview')) {
                throw new Http403Exception('Permission error');
            }

            $ids = $this->imageService->createImagesToReview($this->request->getUploadedFiles(),$review);

            $this->imageService->saveImagesToReview($this->request->getUploadedFiles(),$review,$ids);

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
                case ReviewService::ERROR_REVIEW_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Image was successfully added to review');
    }

    /**
     * Добавляет все отправленные файлы изображений к отзыву. Общее количество
     * фотографий для одного отзыва на данный момент не более 3.
     * Доступ не проверяется.
     *
     * @param $reviewId
     * @return Response с json массивом типа Status
     */
    /*public function addImagesHandler($reviewId)
    {
        include(APP_PATH . '/library/SimpleImage.php');
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $review = Reviews::findFirstByReviewid($reviewId);

            if (!$review) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор отзыва'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $images = ImagesReviews::findByReviewid($reviewId);
            $countImages = count($images);

            if (($countImages + count($files)) > ImagesReviews::MAX_IMAGES) {
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для отзыва. 
                        Можно сохранить для одного отзыва не более чем ' . ImagesReviews::MAX_IMAGES . ' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }
            $this->db->begin();
            $imagesIds = [];
            foreach ($files as $file) {
                $newimage = new ImagesReviews();
                $newimage->setReviewId($reviewId);
                $newimage->setImagePath('magic_string');

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

                $filename = ImageLoader::formFullImageName('reviews', $imageFormat, $reviewId, $newimage->getImageId());

                $newimage->setImagePath($filename);

                if (!$newimage->update()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadReviewImage($file->getTempName(), $file->getName(), $reviewId, $imagesIds[$i]);
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
     *
     */
    public function addTypeAction()
    {
        $query = $this->db->prepare("ALTER TYPE bindertype AS ENUM ('task', 'request',);");

        return $query->execute();
    }

}