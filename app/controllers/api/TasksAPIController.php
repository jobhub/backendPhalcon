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
            //$query = $this->modelsManager->createQuery('SELECT * FROM tasks LEFT OUTER JOIN auctions ON tasks.taskId=auctions.taskId WHERE tasks.userId = :userId:');

            $tasks = Tasks::findByUserId($userId);

            $TasksAndTenders = [];

            for($i = 0; $i < $tasks->count();$i++){

                $auction = Auctions::findFirstByTaskId($tasks[$i]->getTaskId());
                $count = 0;
                if($auction == false) {
                    $auction = null;
                }
                else{
                    //-----------------temporary----AuctionId - может надо будет исправить-------------------
                    $offers = Offers::findByAuctionId($auction->getAuctionId());
                    $count  =$offers->count();
                }
                $TasksAndTenders[] = ['tasks' => $tasks[$i], 'auctions' => $auction,'offersCount' => $count];
            }

            /*$auctions = $query->execute(
                [
                    'userId' => "$userId"
                ]
            );*/

            return json_encode($TasksAndTenders);
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
            $task->setLatitude($this->request->getPut("latitude"));
            $task->setLongitude($this->request->getPut("longitude"));


            $task->setDeadline(date('Y-m-d H:m:s',strtotime($this->request->getPut("deadline"))));
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

            $this->db->commit();
            $taskAndTender['tasks'] = Tasks::findFirstByTaskId($task->getTaskId());
            $taskAndTender['auctions'] = null;
            $response->setJsonContent(
                [
                    "taskAndTender" => $taskAndTender,
                    "status" => "OK"
                ]
            );
            return $response;

        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }

    }

    public function deleteAction($taskId)
    {
        if($this->request->isDelete()){
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $task = Tasks::findFirstByTaskId($taskId);

            if($task->getUserId() == $userId){
                $auction = Auctions::findFirstByTaskId($task->getTaskId());
                if($auction)
                    $offers = Offers::findByAuctionId($auction->getAuctionId());
                if($auction==false || $offers->count()==0){
                    if (!$task->delete()) {

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
                    $response->setJsonContent(
                        [
                            "status" => "OK"
                        ]
                    );
                    return $response;
                }
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => ['Есть предложения исполнения задания']
                    ]
                );
                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => "WRONG_DATA",
                    "errors" => ['Задание не принадлежит пользователю']
                ]
            );
            return $response;
        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function changeAction(){
        if($this->request->isPost()){
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $task = Tasks::findFirstByTaskId($this->request->getPost("taskId"));


            if($userId == $task->getUserId()) {
                $task->setCategoryid($this->request->getPost("categoryId"));
                $task->setName($this->request->getPost("name"));
                $task->setDescription($this->request->getPut("description"));


                $task->setDeadline(date('Y-m-d H:m', strtotime($this->request->getPut("deadline"))));
                $task->setPrice($this->request->getPut("price"));


                if (!$task->save()) {
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

                $response->setJsonContent(
                    [
                        "status" => "OK"
                    ]
                );
                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => "WRONG_DATA",
                    "errors" => ['Задание не принадлежит пользователю']
                ]
            );
            return $response;

        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function getAllTasks(){
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $tasks = Tasks::findByUserId();

        return json_encode($tasks);
    }
}