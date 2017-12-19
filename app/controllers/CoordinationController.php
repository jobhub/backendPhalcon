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
    public function indexAction($auctionId = null)
    {
        $this->persistent->parameters = null;

        if ($auctionId == null) {
            $auctionId = $this->session->get('coord')['auctionId'];
        }

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

    /**
     * Saves a message edited
     *
     */
    public
    function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "messages",
                'action' => 'index'
            ]);

            return;
        }

        $messageId = $this->request->getPost("messageId");
        $message = Messages::findFirstBymessageId($messageId);

        if (!$message) {
            $this->flash->error("message does not exist " . $messageId);

            $this->dispatcher->forward([
                'controller' => "messages",
                'action' => 'index'
            ]);

            return;
        }

        $message->setAuctionid($this->request->getPost("auctionId"));
        $message->setInput($this->request->getPost("input"));
        $message->setMessage($this->request->getPost("message"));
        $message->setDate($this->request->getPost("date"));


        if (!$message->save()) {

            foreach ($message->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "messages",
                'action' => 'edit',
                'params' => [$message->getMessageid()]
            ]);

            return;
        }

        $this->flash->success("message was updated successfully");

        $this->dispatcher->forward([
            'controller' => "messages",
            'action' => 'index'
        ]);
    }


}
