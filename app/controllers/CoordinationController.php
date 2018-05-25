<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class CoordinationController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Согласование');
        parent::initialize();
    }

    /**
     * Index action
     */
    public function indexAction($taskId = null)
    {
        $this->assets->addJs("https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js",false);
        $this->assets->addJs("http://api-maps.yandex.ru/2.1/?lang=ru_RU",false);
        $this->assets->addJs("/public/js/mapTask.js",true);
        $this->assets->addCss("/public/css/style.css",true);

        if ($taskId == null) {
            $auctionId = $this->session->get('coord')['auctionId'];
        }
        else {
            $auction=Auctions::find("taskId=$taskId");
            $auction=$auction->getFirst();
            if (!$auction) {
                $this->flash->error("Такого тендера ещё нет. Создайте! ");

                $this->dispatcher->forward([
                    'controller' => "auctions",
                    'action' => 'new'
                ]);

                return;
            }
            $auctionId = $auction->getAuctionId();
        }
        $this->persistent->parameters = null;
        //Проверка. Не знаю, зачем, но мало ли
        $auth = $this->session->get('auth');

        if ($auth) {
            //Пользователь авторизован
            $auction = Auctions::findFirstByAuctionId($auctionId);
            if ($auction != null && $auction->getSelectedOffer() != null) {
                //Аукцион существует и проведен

                if ($auction->tasks->getUserId() == $auth['id']) {
                    //Пользователь владелец
                    $messages = Messages::find([
                        'auctionId = :auctionId:',
                        'bind' => [
                            'auctionId' => $auctionId
                        ],
                        'order' => ['date']
                    ]);

                    $owner = 1;
                    $otherUser = Users::findFirstByUserId($auction->offers->getUserId());
                } else {
                    $offer = Offers::findFirst([
                        "offerId = :offerId: AND userId = :userId:",
                        "bind" => [
                            "offerId" => $auction->getSelectedOffer(),
                            "userId" => $auth['id'],
                        ]
                    ]);
                    if ($offer) {
                        //Пользователь - исполнитель
                        $messages = Messages::find([
                            'auctionId = :auctionId:',
                            'bind' => [
                                'auctionId' => $auctionId
                            ],
                            'order' => ['date']
                        ]);

                        $otherUser = Users::findFirstByUserId($auction->tasks->getUserId());
                        $owner = 0;
                    } else {
                        //Выдает себя за другого
                        $this->dispatcher->forward([
                            "controller" => "errors",
                            "action" => "show404"
                        ]);
                        return;
                    }

                }
            } else {
                $this->dispatcher->forward([
                    "controller" => "errors",
                    "action" => "show404"
                ]);
                return;
            }
        } else {
            $this->dispatcher->forward([
                "controller" => "errors",
                "action" => "show404"
            ]);
            return;
        }


        $this->view->setVar('owner', $owner);
        $this->view->setVar('otherUser', $otherUser);

        //Для формы
        $task=$auction->tasks;
        //$task=$task->getFirst();
        $categories=Categories::find();
        $this->view->setVar("categories", $categories);
        $this->tag->setDefault("auctionId",$auction->getAuctionId());
        $this->tag->setDefault("name", $task->getName());
        $this->tag->setDefault("categoryId", $task->getCategoryid());
        $this->tag->setDefault("description", $task->getDescription());
        $this->tag->setDefault("address", $task->getaddress());
        $this->tag->setDefault("deadline", $task->getDeadline());
        $this->tag->setDefault("price", $task->getPrice());
        $this->tag->setDefault("dateStart",$auction->getDateStart());
        $this->tag->setDefault("dateEnd",$auction->getDateEnd());

        $this->session->set(
            'coord',
            [
                'auctionId' => $auctionId,
                'owner'=>$owner
            ]
        );
        $this->view->setVar('messages', $messages);

    }


    /**
     * Creates a new message
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "coordination",
                'action' => 'index'
            ]);

            return;
        }

        $message = new Messages();
        $message->setAuctionid($this->session->get('coord')['auctionId']);
        $message->setInput($this->session->get('coord')['owner']==1?0:1);
        $message->setMessage($this->request->getPost("message"));
        $message->setDate(date('Y-m-d H:i:s'));


        if (!$message->save()) {
            foreach ($message->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "coordination",
                'action' => 'index'
            ]);

            return;
        }

        //$this->flash->success("message was created successfully");

        foreach($_POST as $key=>$value){
            unset($_POST[$key]);
        }

        $this->dispatcher->forward([
            'controller' => "coordination",
            'action' => 'index'
        ]);
    }

    public function endAction(){

            $auctionId = $this->request->getPost('auctionId');

            $task = Auctions::findFirstByAuctionId($auctionId)->tasks;

        $auth = $this->session->get('auth');
        $objectId=null;
        if($auth['id']==$task->getUserId())
        {
            //отзыв на исполнителя
            $offer=Offers::findFirstByOfferId($task->auctions[0]->getSelectedOffer());
            $objectId=$offer->getUserId();
            $this->session->set('executor',1);
            $this->session->set('subjectId',$auth['id']);
        }
        else
        {
            //отзыв на заказчика
            $objectId=$task->getUserId();
            $this->session->set('executor',0);
            $offer=Offers::findFirstByOfferId($task->auctions[0]->getSelectedOffer());
            $this->session->set('subjectId',$offer->getUserId());
        }
        $this->session->set('coordination',true);

        $task->setStatus(2);

        if (!$task->save()) {
            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "coordination",
                'action' => 'index'
            ]);

            return;
        }

        $this->dispatcher->forward([
            'controller' => "reviews",
            'action' => 'new',
            'params' => [$objectId]
        ]);
    }


}
