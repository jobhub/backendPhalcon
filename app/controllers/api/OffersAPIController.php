<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

class OffersAPIController extends Controller
{
    /**
     * Index action
     */
    public function getForTenderAction($tenderId)
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $tender = Auctions::findFirstByAuctionId($tenderId);
            //$task = Tasks::findFirstbyTaskId($taskId);
            $task = $tender->tasks;
            if ($task->getUserId() == $userId) {
                $offers = Offers::findByAuctionId($tenderId);

                $offers2 = [];
                for ($k=0;$k<$offers->count();$k++)
                    $offers2[] = $offers[$k];

                //Сортировка
                for($i=1;$i<=count($offers2)-1;$i++){
                    for($j=1;$j<=count($offers2)-$i;$j++){
                        if($offers2[$j-1]->getScore()<$offers2[$j]->getScore()){
                            $offer = $offers2[$j-1];
                            $offers2[$j-1] = $offers2[$j];
                            $offers2[$j] = $offer;
                        }
                    }
                }
                //
                //$offers[2]->getScore();
                $offerWithUser = null;
                if ($offers2) {
                    for ($i = 0; $i < $offers->count(); $i++) {
                        $offer = $offers2[$i];
                        $userinfo = Userinfo::findFirstByUserId($offers2[$i]->getUserId());

                        $offerWithUser[] = ['Offer' => $offer, 'Userinfo' => $userinfo];
                    }
                }

                $response->setJsonContent(
                    [
                        "status" => ["status" => "OK"],
                        "offersWithUser" => $offerWithUser
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
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function addAction()
    {
        if ($this->request->isPut()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $tenderId = $this->request->getPut("tenderId");

            $tender = Auctions::findFirstByAuctionId($tenderId);

            if (!$tender) {
                $response->setJsonContent(
                    [
                        "status" => "FAIL",
                        "errors" => ['Такого тендера не существует']
                    ]
                );
                return $response;
            }

            $offers = Offers::findByAuctionId($tenderId);

            $exists = false;
            foreach ($offers as $offer) {
                if ($offer->getUserId() == $userId) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $response->setJsonContent(
                    [
                        "status" => "FAIL",
                        "errors" => ['Пользователь уже оставил предложение для данного тендера']
                    ]
                );
                return $response;
            }

            $offer = new Offers();

            $offer->setUserId($userId);
            $offer->setAuctionId($tenderId);
            $offer->setDeadline(date('Y-m-d H:i:s', strtotime($this->request->getPut("deadline"))));
            $offer->setPrice($this->request->getPut("price"));
            $offer->setDescription($this->request->getPut("description"));

            if (!$offer->save()) {
                foreach ($offer->getMessages() as $message) {
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
                    "offer" => $offer,
                    "status" => "OK"
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function getForUserAction()
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $offers = Offers::findByUserId($userId);
            $offerWithTask = [];
            if ($offers) {
                for ($i = 0; $i < $offers->count(); $i++) {
                    $offer = $offers[$i];
                    $auction = $offer->Auctions;
                    $task = $offer->auctions->tasks;
                    $userinfo = $task->Users->userinfo;

                    $offerWithTask[] = ['Offer' => $offer,'Tasks' => $task, 'Userinfo' => $userinfo, 'Tender'=> $auction];
                }
            }

            return json_encode($offerWithTask);


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function deleteAction($offerId){
        if($this->request->isDelete()) {
            $offer = Offers::findFirstByOfferId($offerId);

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if($offer->getUserId() == $userId && $offer->getSelected()!=1){
                if (!$offer->delete()) {

                    foreach ($offer->getMessages() as $message) {
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
            } else{
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => ['Предложение не принадлежит пользователю']
                    ]
                );
                return $response;
            }
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

}
