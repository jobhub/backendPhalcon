<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class MessagesController extends ControllerBase
{

    public function initialize()
    {
        $this->tag->setTitle('Сообщения');
        parent::initialize();
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;

        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Messages', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "messageId";

        $messages = Messages::find($parameters);
        if (count($messages) == 0) {
            $this->flash->notice("Не найдено ни одного сообщения");
        }

        $paginator = new Paginator([
            'data' => $messages,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }



    /**
     * Edits a message
     *
     * @param string $messageId
     */
    public function editAction($messageId)
    {
        if (!$this->request->isPost()) {

            $message = Messages::findFirstBymessageId($messageId);
            if (!$message) {
                $this->flash->error("Сообщение не найдено");

                $this->dispatcher->forward([
                    'controller' => "messages",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->messageId = $message->getMessageid();

            $this->tag->setDefault("messageId", $message->getMessageid());
            $this->tag->setDefault("auctionId", $message->getAuctionid());
            $this->tag->setDefault("input", $message->getInput());
            $this->tag->setDefault("message", $message->getMessage());
            $this->tag->setDefault("date", $message->getDate());
            
        }
    }


    /**
     * Saves a message edited
     *
     */
    public function saveAction()
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
            $this->flash->error("Сообщение с ID " . $messageId . " не существует");

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

        $this->flash->success("Сообщение успешно изменено");

        $this->dispatcher->forward([
            'controller' => "messages",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a message
     *
     * @param string $messageId
     */
    public function deleteAction($messageId)
    {
        $message = Messages::findFirstBymessageId($messageId);
        if (!$message) {
            $this->flash->error("Сообщение не найдено");

            $this->dispatcher->forward([
                'controller' => "messages",
                'action' => 'index'
            ]);

            return;
        }

        if (!$message->delete()) {

            foreach ($message->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "messages",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("Сообщение успешно удалено");

        $this->dispatcher->forward([
            'controller' => "messages",
            'action' => "index"
        ]);
    }

}
