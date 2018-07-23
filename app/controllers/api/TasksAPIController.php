<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

class TasksAPIController extends Controller
{
    /**
     * Добавляет заказ
     *
     * @method POST
     *
     * @params (обязательные) categoryId, name, price.
     * @params (необязательные) companyId, description, deadline, polygon, regionId, longitude, latitude.
     *
     * @return string - json array  формате Status
     */
    public function addTaskAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $task = new Tasks();

            if ($this->request->getPost("companyId")) {
                //Значит, от лица компании
                if (!Companies::checkUserHavePermission($userId,
                    $this->request->getPost("companyId"), 'addTask')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }
                $task->setSubjectId($this->request->getPost("companyId"));
                $task->setSubjectType(1);
            } else {
                $task->setSubjectId($userId);
                $task->setSubjectType(0);
            }

            $task->setCategoryid($this->request->getPost("categoryId"));
            $task->setName($this->request->getPost("name"));
            $task->setDescription($this->request->getPost("description"));
            $task->setDeadline(date('Y-m-d H:i:s', strtotime($this->request->getPost("deadline"))));
            $task->setPrice($this->request->getPost("price"));
            $task->setStatus(STATUS_WAIT);
            $task->setPolygon($this->request->getPost("polygon"));
            $task->setRegionId($this->request->getPost("regionId"));
            $task->setLatitude($this->request->getPost("latitude"));
            $task->setLongitude($this->request->getPost("longitude"));

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
     * Возвращает все задания субъекта (для него самого)
     *
     * @method GET
     *
     * @param $companyId
     *
     * @return string - массив заданий (Tasks) и Status
     *
     */
    public function getTasksForCurrentUserAction($companyId = null)
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if ($companyId == null)
                $tasks = Tasks::find(["subjectId = :userId: AND subjectType = 0", "bind" => ["userId" => $userId],
                    "order" => "status ASC"]);
            else {
                if (!Companies::checkUserHavePermission($userId, $companyId, 'getTasks')) {

                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $tasks = Tasks::find(["subjectId = :companyId: AND subjectType = 1",
                    "bind" => ["companyId" => $companyId],
                    "order" => "status ASC"]);
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    "tasks" => $tasks
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }

    }

    /**
     * Возвращает все задания указанного субъекта
     *
     * @method GET
     *
     * @param $subjectId
     * @param $subjectType
     *
     * @return string - массив заданий (Tasks)
     */
    public function getTasksForSubjectAction($subjectId, $subjectType)
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $tasks = Tasks::find(["subjectId = :subjectId: AND subjectType = 0 AND status = :status:",
                "bind" => ["subjectId" => $userId, 'status' => STATUS_WAIT],
                "order" => "status ASC"]);

            return json_encode($tasks);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }

    }

    /**
     * Удаление заказа
     *
     * @method DELETE
     * @param $taskId
     * @return string - json array в формате Status
     */
    public function deleteTaskAction($taskId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $task = Tasks::findFirstByTaskId($taskId);

            if (!$task) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Задание не существует']
                    ]
                );
                return $response;
            }

            if (!Tasks::checkUserHavePermission($userId, $taskId, 'deleteTask')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$task->delete()) {
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
     * Редактирование задания
     *
     * @method PUT
     * @params (обязательные) taskId, categoryId, name, price.
     * @params (необязательные)  description, deadline, polygon, regionId, longitude, latitude.
     * @return string - json array в формате Status
     */
    public function editTaskAction()
    {
        if ($this->request->isPut()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $task = Tasks::findFirstByTaskId($this->request->getPut("taskId"));

            if (!$task) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Задание не существует']
                    ]
                );
                return $response;
            }

            if (!Tasks::checkUserHavePermission($userId, $task->getTaskId(), 'editTask')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $task->setCategoryid($this->request->getPut("categoryId"));
            $task->setName($this->request->getPut("name"));
            $task->setDescription($this->request->getPut("description"));
            $task->setDeadline(date('Y-m-d H:i:s', strtotime($this->request->getPut("deadline"))));
            $task->setPrice($this->request->getPut("price"));
            $task->setPolygon($this->request->getPut("polygon"));
            $task->setRegionId($this->request->getPut("regionId"));
            $task->setLatitude($this->request->getPut("latitude"));
            $task->setLongitude($this->request->getPut("longitude"));

            if (!$task->save()) {
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
     * Выбирает предложение для выполнения заказа
     * @method POST
     * @params taskId, offerId
     * @return string - json array в формате Status
     */
    public function selectOfferAction(){
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if(!Tasks::checkUserHavePermission($userId,$this->request->getPost('taskId'),'selectOffer')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $offer = Offers::findFirstByOfferId($this->request->getPost('offerId'));

            if(!$offer || $offer->getTaskId()!= $this->request->getPost('taskId')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Предложение не существует или не относится к указанному заданию']
                    ]
                );
                return $response;
            }

            $offer->setSelected(true);
            $task = $offer->tasks;
            $task->setStatus(STATUS_EXECUTING);
            $this->db->begin();

            if (!$offer->save()) {
                $errors = [];
                $this->db->rollback();
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

            if (!$task->save()) {
                $this->db->rollback();
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

            $this->db->commit();

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