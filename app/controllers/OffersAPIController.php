<?php

namespace App\Controllers;

use App\Models\Offers;
use App\Services\OfferService;
use App\Services\TaskService;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
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
 * Контроллер для работы с предложениями.
 * Реализует CRUD для работы с предложениями.
 * Есть методы для изменения статуса выполнения заказа с точки зрения исполнителя:
 *      - подтвердил согласие выполнения;
 *      - отказался выполнить задание;
 *      - подтверждает выпоолнение задания.
 */
class OffersAPIController extends AbstractController
{
    /**
     * Возвращает предложения для определенного задания
     * @method GET
     * @param $task_id
     *
     * @return string - json array объектов Offers
     */
    public function getForTaskAction($task_id)
    {
        try {
            $userId = self::getUserId();
            $task = $this->taskService->getTaskById($task_id);

            if (!Accounts::checkUserHavePermission($userId, $task->getAccountId(), 'getOffersForTask')) {
                throw new Http403Exception('Permission error');
            }

            return Offers::findOffersForTask($task_id);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case TaskService::ERROR_TASK_NOT_FOUND:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Добавляет предложение на выполнение указанного задания
     *
     * @method POST
     *
     * @params (Обязательные) task_id, deadline, price.
     * @params (Необязательные) description, account_id.
     *
     * @return string - json array в формате Status
     */
    public function addOfferAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['task_id'] = $inputData->task_id;
        $data['deadline'] = $inputData->deadline;
        $data['price'] = $inputData->price;
        $data['description'] = $inputData->description;
        $data['account_id'] = $inputData->account_id;

        try {
            $userId = self::getUserId();

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addOffer')) {
                throw new Http403Exception('Permission error');
            }

            //validation
            if (empty(trim($data['task_id']))) {
                $errors['task_id'] = 'Missing required parameter "task_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $task = $this->taskService->getTaskById($data['task_id']);

            if ($task->getStatus() != STATUS_ACCEPTING) {
                $errors['errors'] = true;
                $errors['task_id'] = 'Time to filling application is over';
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $offer = $this->offerService->createOffer($data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case OfferService::ERROR_UNABLE_CREATE_OFFER:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case TaskService::ERROR_TASK_NOT_FOUND:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Offer was successfully created', ['offer' => $offer->toArray()]);
    }

    /**
     * Возвращает офферсы субъекта
     *
     * @method GET
     *
     * @param $company_id (необязательный). Если не отправить, будут возвращены для текущего пользователя
     *
     * @return string - json array объектов Offers
     */
    public function getForCurrentUserAction($company_id = null)
    {
        $userId = self::getUserId();

        if ($company_id != null) {
            if (!Accounts::checkUserHavePermissionToCompany($userId, $company_id, 'getOffers')) {
                throw new Http403Exception('Permission error');
            }

            return Offers::findOffersByCompany($company_id);
        }
        else
            return Offers::findOffersByUser($userId);
    }

    /**
     * Удаляет предложение на выполнение заявки
     *
     * @method DELETE
     * @param $offer_id
     *
     * @return string - json array в формате Status
     */
    public function deleteOfferAction($offer_id)
    {
        try {
            $userId = self::getUserId();

            $offer = $this->offerService->getOfferById($offer_id);

            if (!Accounts::checkUserHavePermission($userId, $offer->getAccountId(), 'deleteOffer')) {
                throw new Http403Exception('Permission error');
            }

            if($offer->getSelected()){
                $errors['offer_id'] = 'Offer already selected';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $this->offerService->deleteOffer($offer);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case OfferService::ERROR_UNABLE_DELETE_OFFER:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case OfferService::ERROR_OFFER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Offer was successfully deleted');
    }

    /**
     * Редактирует предложение на выполнение указанного задания
     *
     * @method PUT
     *
     * @params (Обязательные) offer_id, deadline, price.
     * @params (Необязательные) description.
     *
     * @return string - json array в формате Status
     */
    public function editOfferAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['offer_id'] = $inputData->offer_id;
        $data['deadline'] = $inputData->deadline;
        $data['description'] = $inputData->description;
        $data['price'] = $inputData->price;

        try {
            //validation
            if (empty(trim($data['offer_id']))) {
                $errors['offer_id'] = 'Missing required parameter "offer_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $userId = self::getUserId();

            $offer = $this->offerService->getOfferById($data['offer_id']);

            if (!Accounts::checkUserHavePermission($userId, $offer->getAccountId(), 'editOffer')) {
                throw new Http403Exception('Permission error');
            }

            if($offer->tasks->getStatus()!=STATUS_ACCEPTING){
                $errors['offer_id'] = 'Task already in executing';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            unset($data['offer_id']);

            $this->offerService->changeOffer($offer, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case OfferService::ERROR_UNABLE_CHANGE_OFFER:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case OfferService::ERROR_OFFER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Offer was successfully changed');
    }

    //TODO - переделать позже
    /**
     * Подтверждает согласие исполнителя выполнить задание
     *
     * @method PUT
     *
     * @params offerId
     *
     * @return string - json array в формате Status
     */
    public function confirmOfferAction(){
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $offer = Offers::findFirstByOfferid($this->request->getPut("offerId"));
            if(!$offer ||!SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId,$offer->getSubjectId(),$offer->getSubjectType(),'confirmOffer')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$offer->confirm()) {
                $errors = [];
                foreach ($offer->getMessages() as $message) {
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
                    "status" => STATUS_OK
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Исполнитель отказывается от своего первоначального намерения выполнить заказ
     *
     * @method PUT
     *
     * @params offerId
     *
     * @return string - json array в формате Status
     */
    public function rejectOfferAction(){
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $offer = Offers::findFirstByOfferid($this->request->getPut("offerId"));
            if(!$offer || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId,$offer->getSubjectId(),$offer->getSubjectType(),'rejectOffer')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }


            if (!$offer->reject()) {
                $errors = [];
                foreach ($offer->getMessages() as $message) {
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
                    "status" => STATUS_OK
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Исполнитель утверждает, что выполнил заказ
     *
     * @method PUT
     *
     * @params offerId
     *
     * @return string - json array в формате Status
     */
    public function performTaskAction(){
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $offer = Offers::findFirstByOfferid($this->request->getPut("offerId"));

            if(!$offer || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId,$offer->getSubjectId(),$offer->getSubjectType(),
                    'performTask')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $task = $offer->tasks;

            if($task->getStatus() != STATUS_EXECUTING){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя отметить задание как выполненное в текущем состоянии']
                    ]
                );
                return $response;
            }

            $task->setStatus(STATUS_EXECUTED_EXECUTOR);

            if (!$task->update()) {
                $errors = [];
                foreach ($task->getMessages() as $message) {
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
                    "status" => STATUS_OK
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function addStatusAction()
    {
        $status = new Statuses();
        $status->setStatus($this->request->getPost('status'));
        $status->setStatusId($this->request->getPost('statusId'));
        return $status->save();
    }
}
