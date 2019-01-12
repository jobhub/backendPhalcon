<?php

namespace App\Controllers;

use App\Models\Requests;
use App\Services\RequestService;
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
 * Class RequestsAPIController
 * Контроллер для работы с заявками на получение услуги.
 * Реализует CRUD для запросов и
 * методы изменения статуса выполнения заявки с точки зрения клиента (не все статусы):
 *      - отмена заявки;
 *      - подтверждение выполнения заявки.
 */
class RequestsAPIController extends AbstractController
{
    /**
     * Добавляет запрос на получение услуги
     *
     * @method POST
     *
     * @params service_id, description, date_end.
     * @params (необязательный) account_id
     * @return Response с json массивом в формате Status
     */
    public function addRequestAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['service_id'] = $inputData->service_id;
        $data['date_end'] = $inputData->date_end;
        $data['description'] = $inputData->description;
        $data['account_id'] = $inputData->account_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addRequest')) {
                throw new Http403Exception('Permission error');
            }

            $data['status'] = STATUS_WAITING_CONFIRM;

            $request = $this->requestService->createRequest($data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case RequestService::ERROR_UNABLE_CREATE_REQUEST:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case RequestService::ERROR_REQUEST_NOT_FOUND:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Request was successfully created', ['request_id' => $request->getRequestId()]);
    }

    /**
     * Удаляет заявку
     *
     * @method DELETE
     *
     * @param $request_id
     * @return Response с json массивом в формате Status
     */
    public function deleteRequestAction($request_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = $this->requestService->getRequestById($request_id);

            if (!Accounts::checkUserHavePermission($userId, $request->getAccountId(), 'deleteRequest')) {
                throw new Http403Exception('Permission error');
            }

            $this->requestService->deleteRequest($request);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case RequestService::ERROR_UNABLE_DELETE_REQUEST:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case RequestService::ERROR_REQUEST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Request was successfully deleted');
    }

    /**
     * Редактирует заявку
     *
     * @method PUT
     *
     * @params request_id, description, date_end
     * @return Response с json массивом в формате Status
     */
    public function editRequestAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['request_id'] = $inputData->request_id;
        $data['description'] = $inputData->description;
        $data['date_end'] = $inputData->date_end;

        try {

            //validation
            if (empty(trim($data['request_id']))) {
                $errors['request_id'] = 'Missing required parameter "request_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = $this->requestService->getRequestById($data['request_id']);

            if (!Accounts::checkUserHavePermission($userId, $request->getAccountId(), 'editRequest')) {
                throw new Http403Exception('Permission error');
            }

            unset($data['request_id']);

            $this->requestService->changeRequest($request, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case RequestService::ERROR_UNABLE_CHANGE_REQUEST:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case RequestService::ERROR_REQUEST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Request was successfully changed');
    }

    /**
     * Редактирует заявку
     *
     * @method GET
     *
     * @param $company_id (необязательный)
     * @return string - json массив с объектами Requests и Status-ом
     */
    public function getRequestsAction($company_id = null)
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        if ($company_id != null) {
            if (!Accounts::checkUserHavePermissionToCompany($userId, $company_id, 'getRequest')) {
                throw new Http403Exception('Permission error');
            }

            return Requests::findRequestByCompany($company_id);
        }
        else
            return Requests::findRequestByUser($userId);
    }

    //TODO - заменить и эти 2 action-а тоже

    /**
     * Заказчик отменяет заявку.
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return string - json array в формате Status
     */
    public function cancelRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            if (!$request || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId, $request->getSubjectId(),
                    $request->getSubjectType(), 'cancelRequest')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_WAITING_CONFIRM &&
                $request->getStatus() != STATUS_EXECUTING &&
                $request->getStatus() != STATUS_EXECUTED_EXECUTOR) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя отменить заказ на данном этапе']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() == STATUS_WAITING_CONFIRM)
                $request->setStatus(STATUS_CANCELED);
            else {
                $request->setStatus(STATUS_NOT_EXECUTED);
            }

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
     * Заказчик подтверждает выполнение заявки
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return string - json array в формате Status
     */
    public function confirmPerformanceRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            if (!$request || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId, $request->getSubjectId(),
                    $request->getSubjectType(), 'cancelRequest')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_EXECUTED_EXECUTOR && $request->getStatus() != STATUS_EXECUTING) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя на данном этапе подтвердить выполнение заказа']
                    ]
                );
                return $response;
            }

            $request->setStatus(STATUS_EXECUTED_CLIENT);

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
}
