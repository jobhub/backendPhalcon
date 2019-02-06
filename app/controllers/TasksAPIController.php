<?php

namespace App\Controllers;

use App\Models\Tasks;
use App\Services\OfferService;
use App\Services\TaskService;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;
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
 * Class TasksAPIController
 * Контроллер для работы с заказами.
 * Реализует CRUD для заказов, метод для выбора предложения для выполнения заказа,
 * а также содержит методы для изменения статуса заказа:
 *      - отмена заказа;
 *      - подтверждение выполнения заказа;
 */
class TasksAPIController extends AbstractController
{
    /**
     * Добавляет заказ
     *
     * @method POST
     *
     * @params (обязательные) category_id, name, price, date_end.
     * @params (необязательные) account_id, description, deadline, polygon, region_id, longitude, latitude.
     *
     * @return string - json array  формате Status
     */
    public function addTaskAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['category_id'] = $inputData->category_id;
        $data['name'] = $inputData->name;
        $data['price'] = $inputData->price;
        $data['date_end'] = $inputData->date_end;
        $data['account_id'] = $inputData->account_id;
        $data['description'] = $inputData->description;
        $data['deadline'] = $inputData->deadline;
        $data['polygon'] = $inputData->polygon;
        $data['region_id'] = $inputData->region_id;
        $data['longitude'] = $inputData->longitude;
        $data['latitude'] = $inputData->latitude;

        try {
            $userId = self::getUserId();

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addRequest')) {
                throw new Http403Exception('Permission error');
            }

            $data['status'] = STATUS_ACCEPTING;
            $data['date_start'] = date('Y-m-d H:i:s');

            $task = $this->taskService->createTask($data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case TaskService::ERROR_UNABLE_CREATE_TASK:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Task was successfully created', ['task' => $task->toArray()]);
    }

    /**
     * Возвращает все задания субъекта (для него самого)
     *
     * @method GET
     *
     * @param $company_id
     *
     * @return string - массив заданий (Tasks) и Status
     *
     */
    public function getTasksForCurrentUserAction($company_id = null)
    {
        $userId = self::getUserId();

        if ($company_id != null) {
            if (!Accounts::checkUserHavePermissionToCompany($userId, $company_id, 'getTask')) {
                throw new Http403Exception('Permission error');
            }

            return Tasks::findTasksByCompany($company_id);
        }
        else
            return Tasks::findTasksByUser($userId);
    }

    /**
     * Возвращает все задания указанного субъекта
     *
     * @method GET
     *
     * @param $id
     * @param $is_company
     *
     * @return string - массив заданий (Tasks)
     */
    public function getTasksForSubjectAction($id, $is_company = false)
    {
        if ($is_company && strtolower($is_company)!="false")
            return Tasks::findAcceptingTasksByCompany($id);
        else
            return Tasks::findAcceptingTasksByUser($id);
    }

    /**
     * Удаление заказа
     *
     * @method DELETE
     * @param $task_id
     * @return string - json array в формате Status
     */
    public function deleteTaskAction($task_id)
    {
        try {
            $userId = self::getUserId();

            $task = $this->taskService->getTaskById($task_id);

            if (!Accounts::checkUserHavePermission($userId, $task->getAccountId(), 'deleteTask')) {
                throw new Http403Exception('Permission error');
            }

            $this->taskService->deleteTask($task);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case TaskService::ERROR_UNABLE_DELETE_TASK:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case TaskService::ERROR_TASK_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Task was successfully deleted');
    }

    /**
     * Редактирование задания
     *
     * @method PUT
     * @params (обязательные) task_id.
     * @params (необязательные)  description, deadline, polygon,
     *                           region_id, longitude, latitude,
     *                           category_id, name, price, date_end.
     * @return string - json array в формате Status
     */
    public function editTaskAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['task_id'] = $inputData->task_id;
        $data['description'] = $inputData->description;
        $data['deadline'] = $inputData->deadline;
        $data['polygon'] = $inputData->polygon;
        $data['region_id'] = $inputData->region_id;
        $data['longitude'] = $inputData->longitude;
        $data['latitude'] = $inputData->latitude;
        $data['category_id'] = $inputData->category_id;
        $data['name'] = $inputData->name;
        $data['price'] = $inputData->price;
        $data['date_end'] = $inputData->date_end;

        try {
            //validation
            if (empty(trim($data['task_id']))) {
                $errors['task_id'] = 'Missing required parameter "task_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $userId = self::getUserId();

            $task = $this->taskService->getTaskById($data['task_id']);

            if (!Accounts::checkUserHavePermission($userId, $task->getAccountId(), 'editTask')) {
                throw new Http403Exception('Permission error');
            }

            unset($data['task_id']);

            $this->taskService->changeTask($task, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case TaskService::ERROR_UNABLE_CHANGE_TASK:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case TaskService::ERROR_TASK_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Task was successfully changed');
    }

    /**
     * Выбирает предложение для выполнения заказа
     *
     * @method POST
     * @params offer_id
     * @return string - json array в формате Status
     */
    public function selectOfferAction(){

        $inputData = $this->request->getJsonRawBody();
        $data['offer_id'] = $inputData->offer_id;

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

            if (!Accounts::checkUserHavePermission($userId, $offer->tasks->getAccountId(), 'selectOffer')) {
                throw new Http403Exception('Permission error');
            }
            $this->db->begin();

            $this->taskService->selectOffer($offer);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case OfferService::ERROR_UNABLE_CHANGE_OFFER:
                case TaskService::ERROR_UNABLE_CHANGE_TASK:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case OfferService::ERROR_OFFER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();
        return self::successResponse('Offer was successfully selected');
    }

    //TODO доделать это позже
    /**
     * Отменяет заказ
     *
     * @method PUT
     *
     * @params $taskId
     * @return string - json array в формате Status
     */
    public function cancelTaskAction(){
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $task = Tasks::findFirstByTaskid($this->request->getPut('taskId'));
            if(!$task || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId,$task->getSubjectId(),$task->getSubjectType(),'rejectTask')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }
            if($task->getStatus() == STATUS_WAITING_CONFIRM ||
                $task->getStatus() == STATUS_NOT_CONFIRMED || $task->getStatus() == STATUS_ACCEPTING){

                $task->setStatus(STATUS_CANCELED);
            } else if($task->getStatus() == STATUS_EXECUTING || $task->getStatus()== STATUS_EXECUTED_EXECUTOR){
                $task->setStatus(STATUS_NOT_EXECUTED);
            } else{
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['На данном этапе заказ не может быть отменен']
                    ]
                );
                return $response;
            }

            if (!$task->save()) {
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

    /**
     * Подтверждает выполнение заказа
     *
     * @method PUT
     *
     * @params $taskId
     * @return string - json array в формате Status
     */
    public function confirmPerformanceTaskAction(){
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $task = Tasks::findFirstByTaskid($this->request->getPut('taskId'));
            if(!$task || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId,$task->getSubjectId(),$task->getSubjectType(),'rejectTask')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }
            if($task->getStatus() != STATUS_EXECUTED_EXECUTOR && $task->getStatus() != STATUS_EXECUTING){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['На данном этапе нельзя подтвердить выполнение заказа']
                    ]
                );
                return $response;
            }

            $task->setStatus(STATUS_EXECUTED_CLIENT);

            if (!$task->save()) {
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
}