<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


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
            $tender = Auctions::findFirstByTenderId($tenderId);
            //$task = Tasks::findFirstbyTaskId($taskId);
            $task = $tender->tasks;
            if ($task->getUserId() == $userId) {
                $offers = Offers::findByTenderId($tenderId);
                $offerWithUser = null;
                if($offers) {
                    for($i = 0; $i < $offers->count(); $i++) {
                        $offer = $offers[$i];
                        $userinfo = Userinfo::findFirstByUserId($offers[$i]->getUserId());

                        $offerWithUser[] = ['Offer' => $offer,'Userinfo'=>$userinfo];
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
        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

}
