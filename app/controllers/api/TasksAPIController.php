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
     * Index action
     */
    public function indexAction()
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            //$today = date("Y-m-d");
            //$query = $this->modelsManager->createQuery('SELECT * FROM tasks LEFT OUTER JOIN auctions ON tasks.taskId=auctions.taskId WHERE tasks.userId = :userId:');

            $tasks = Tasks::find(["userId = :userId:","bind" =>["userId" =>$userId],
                "order" => "status ASC"]);

            $TasksAndTenders = [];

            for($i = 0; $i < $tasks->count();$i++){

                $auction = Auctions::findFirstByTaskId($tasks[$i]->getTaskId());
                //$offers = Offers::findByAuctionId($auction->getAuctionId());
                $count = 0;
                if($auction == false) {
                    $auction = null;
                    $offers = [];
                    $offerWithUser = null;
                }
                else{
                    $offers = Offers::findByAuctionId($auction->getAuctionId());
                    $offerWithUser = null;
                    if ($offers) {
                        for ($j = 0; $j < $offers->count(); $j++) {
                            $offer = $offers[$j];
                            $userinfo = Userinfo::findFirstByUserId($offers[$j]->getUserId());

                            $offerWithUser[] = ['Offer' => $offer, 'Userinfo' => $userinfo];
                        }
                    }
                }
                $TasksAndTenders[] = ['tasks' => $tasks[$i], 'auctions' => $auction,'offers' => $offerWithUser];
                //$TasksAndTenders[] = ['tasks' => $tasks[$i], 'auctions' => $auction,'offerCount' => $count];


            }
            /*$response = new Response();

            $response->setJsonContent(
                [
                    "offersWithUser" => $TasksAndTenders
                ]
            );
            return $response;*/

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

            $task->setAddress($this->request->getPut("address"));


            $task->setDeadline(date('Y-m-d H:i:s',strtotime($this->request->getPut("deadline"))));
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
                $task->setDescription($this->request->getPost("description"));
                $task->setDescription($this->request->getPost("description"));
                $task->setLatitude($this->request->getPost("latitude"));
                $task->setLongitude($this->request->getPost("longitude"));


                $task->setDeadline(date('Y-m-d H:i:s', strtotime($this->request->getPost("deadline"))));
                $task->setPrice($this->request->getPost("price"));


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

    /*public function selectExecutorAction(){
        if($this->request->isPost()){
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $task = Tasks::findFirstByTaskId($this->request->getPost("taskId"));
            $offer = Offers::findFirstByOfferId($this->request->getPost("offerId"));
            $auction = Auctions::findFirstByTaskId($task->getTaskId());

            if($userId == $task->getUserId()) {
               if($offer->getAuctionId() == $auction->getAuctionId()) {
                   $task->setStatus(1);
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
                        "errors" => ['Предложение не относится к данному тендеру']
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
    }*/
}