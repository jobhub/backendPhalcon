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
            $query = $this->modelsManager->createQuery('SELECT * FROM Auctions, Tasks WHERE Tasks.status=\'Поиск\' AND Tasks.taskId=Auctions.taskId AND Auctions.dateEnd>:today:');

            $auctions = $query->execute(
                [
                    'today' => "$today",
                ]
            );

            return json_encode($auctions);
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

                if ($tenderOld != null) {

                    $tender->setTaskId($this->request->getPut("taskId"));
                    $tender->setDateStart(date('Y-m-d H:m'));
                    $tender->setDateEnd(date('Y-m-d H:m', strtotime($this->request->getPut("dateEnd"))));
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

            $auction = Auctions::findFirstByTenderId($tenderId);

            if($auction->tasks->getUserId() == $userId){
                //$auction = Auctions::findFirstByTaskId($task->getTaskId());
                $offers = Offers::findByTenderId($auction->getTenderId());
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