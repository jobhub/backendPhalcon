<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;


class TasksAPIController extends Controller
{

    /**
     * Index action
     */
    public function indexAction()
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            //$today = date("Y-m-d");
            $query = $this->modelsManager->createQuery('SELECT * FROM tasks LEFT OUTER JOIN tender ON tasks.taskId=tender.taskId WHERE tasks.userId = :userId:');

            $auctions = $query->execute(
                [
                    'userId' => "$userId"
                ]
            );

            return json_encode($auctions);
        } else if ($this->request->isPut()) {

            $this->db->begin();
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $task = new Tasks();


            $task->setUserid($userId);
            $task->setCategoryid($this->request->getPut("categoryId"));
            $task->setName($this->request->getPut("name"));
            $task->setDescription($this->request->getPut("description"));


            $task->setDeadline(date('Y-m-d H:m',strtotime($this->request->getPut("deadline"))));
            $task->setPrice($this->request->getPut("price"));


            if (!$task->save()) {
                $this->db->rollback();
                foreach ($task->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }
            $tender = new Tender();

            //$today = date("d-m-Y h:m");
            $tender->setTaskId($task->getTaskId());
            $tender->setDateStart(date('Y-m-d H:m'));
            $tender->setDateEnd(date('Y-m-d H:m',strtotime($this->request->getPut("dateEnd"))));
            //$tender->setDateStart($today);


            if (!$tender->save()) {

                $this->db->rollback();
                foreach ($tender->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }

                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }
            $this->db->commit();
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        }else if($this->request->isDelete()){

        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }

    }

    public function addAction()
    {

    }
}