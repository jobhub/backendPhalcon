<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class TenderAPIController extends Controller
{
    /**
     * Index action
     */
    public function indexAction()
    {
        if ($this->request->isGet()) {
            $today = date("Y-m-d");
            /*$query = $this->modelsManager->createQuery('SELECT * FROM Auctions, Tasks WHERE Tasks.status=\'Поиск\' AND Tasks.taskId=Auctions.taskId AND Auctions.dateEnd>:today:');

            $auctions = $query->execute(
                [
                    'today' => "$today",
                ]
            );*/

            $auctions = Auctions::find("dateEnd > '$today'");

            $auctionAndTask = null;

            foreach ($auctions as $auction){
                $task = $auction->tasks;
                $user = Userinfo::findFirstByUserId($task->getUserId());

                $auctionAndTask[] = ['tender' => $auction, 'tasks' => $task, 'userinfo' => $user];
            }

            /*$response = new Response();

            if($auctionAndTask != null){
                $response->setJsonContent(
                    [
                        "otherAuctions" => $auctionAndTask
                    ]
                );
            }
            else{
                $response->setJsonContent(
                    [
                        "otherAuctions" => '[]'
                    ]
                );
            }

            return $response;*/
            return json_encode($auctionAndTask);


        } else if ($this->request->isPut()) {
            $response = new Response();
            $tender = new Auctions();

            //$today = date("d-m-Y h:m");
            /*$queryUserId = $this->modelsManager->createQuery('SELECT Tasks.userId FROM Tasks WHERE Tasks.taskId=:taskId:');

            $userId = $queryUserId->execute(
                [
                    'taskId' => $this->request->getPut("taskId"),
                ]
            );*/
            $task = Tasks::findFirstByTaskId($this->request->getPut("taskId"));

            if ($task->getUserId() == $this->session->get('auth')['id']) {

                $tenderOld = Auctions::findFirstByTaskId($this->request->getPut("taskId"));

                if (!$tenderOld) {

                    $tender->setTaskId($this->request->getPut("taskId"));
                    $tender->setDateStart(date('Y-m-d H:m:s'));
                    $tender->setDateEnd(date('Y-m-d H:m:s', strtotime($this->request->getPut("dateEnd"))));
                    //$tender->setDateStart($today);


                    if (!$tender->save()) {

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

                    $response->setJsonContent(
                        [
                            "auction" => $tender,
                            "status" => "OK"
                        ]
                    );
                    return $response;
                } else {
                    $response->setJsonContent(
                        [
                            "errors" => ['Тендер уже создан'],
                            "status" => "WRONG_DATA"
                        ]
                    );
                    return $response;
                }
            }
            $response->setJsonContent(
                [
                    "status" => "WRONG_DATA"
                ]
            );
            return $response;
        }

        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function deleteAction($tenderId){
    if($this->request->isDelete()){
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $auction = Auctions::findFirstByAuctionId($tenderId);

            if($auction->tasks->getUserId() == $userId){
                //$auction = Auctions::findFirstByTaskId($task->getTaskId());
                $offers = Offers::findByAuctionId($auction->getAuctionId());
                /*if($offers->count()==0){*/
                if (!$auction->delete()) {

                    foreach ($auction->getMessages() as $message) {
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
                /* }
                 $response->setJsonContent(
                     [
                         "status" => "WRONG_DATA",
                         "errors" => ['Есть предложения исполнения задания']
                     ]
                 );
                 return $response;*/
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
}