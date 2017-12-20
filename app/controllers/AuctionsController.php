<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class AuctionsController extends ControllerBase
{

    public function initialize()
    {
        $this->tag->setTitle('Аукционы');
        parent::initialize();
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;


        $today = date("Y-m-d");
        $query = $this->modelsManager->createQuery('SELECT * FROM Auctions, Tasks WHERE Tasks.status=\'Поиск\' AND Tasks.taskId=Auctions.taskId AND Auctions.dateEnd>:today:');

        $auctions= $query->execute(
            [
                'today' => "$today",
            ]
        );

        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Auctions', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        if (count($auctions) == 0) {
            $this->flash->notice("Такого тендера не существует");
        }

        $paginator = new Paginator([
            'data' => $auctions,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Searches for auctions
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Auctions', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "auctionId";

        $auctions = Auctions::find($parameters);
        if (count($auctions) == 0) {
            $this->flash->notice("Такого тендера не существует");

            $this->dispatcher->forward([
                "controller" => "auctions",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $auctions,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Displays the creation form
     */
    public function newAction($taskId)
    {

        $task=Tasks::find($taskId);
        $task=$task->getFirst();

        if($task==false)
        {
            $this->flash->notice("Задание не существует");

            $this->dispatcher->forward([
                "controller" => "tasks",
                "action" => "mytasks"
            ]);

            return;
        }

        $taskId=$task->getTaskId();
        $auctions=Auctions::find("taskId=$taskId");
        if (count($auctions) == 0) {
            $this->view->setVar("task", $task);

            $this->session->set("taskId", $task->getTaskId());

            $categories = Categories::find();
            $this->view->setVar("categories", $categories);
            // $this->tag->setDefault()
            $this->tag->setDefault("name", $task->getName());
            $this->tag->setDefault("categoryId", $task->getCategoryid());
            $this->tag->setDefault("description", $task->getDescription());
            $this->tag->setDefault("address", $task->getaddress());
            $this->tag->setDefault("deadline", $task->getDeadline());
            $this->tag->setDefault("price", $task->getPrice());

        }
        else {

            $auctions=$auctions->getFirst();
            $this->flash->notice("Вы уже создали тендер по этому заданию");

            $this->dispatcher->forward([
                "controller" => "auctions",
                "action" => "viewing",
                'params' => [$auctions->getAuctionid()]
            ]);

            return;

        }

    }

    /**
     * Edits a auction
     *
     * @param string $auctionId
     */
    public function editAction($auctionId)
    {
        if (!$this->request->isPost()) {

            $auction = Auctions::findFirstByauctionId($auctionId);
            if (!$auction) {
                $this->flash->error("Такого тендера не существует");

                $this->dispatcher->forward([
                    'controller' => "auctions",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->auctionId = $auction->getAuctionid();

            $this->tag->setDefault("auctionId", $auction->getAuctionid());
            $this->tag->setDefault("taskId", $auction->getTaskid());
            $this->tag->setDefault("selectedOffer", $auction->getSelectedoffer());
            $this->tag->setDefault("dateStart", $auction->getDatestart());
            $this->tag->setDefault("dateEnd", $auction->getDateend());
            
        }
    }

    /**
     * Creates a new auction
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }

        $auction = new Auctions();
        $taskId=null;
        if($this->session->get("taskId")!='') {
            $taskId = $this->session->get("taskId");
            $this->session->remove("taskId");
        }

        $today = date("Y-m-d");
        $auction->setTaskid($taskId);
        $auction->setDatestart($this->request->getPost("dateStart"));
        $auction->setDateend($this->request->getPost("dateEnd"));
        $auction->setDateStart($today);
        

        if (!$auction->save()) {
            foreach ($auction->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("Тендер был создан успешно");

        $userid=$this->session->get("auth");
        $userid=$userid['id'];

        $this->dispatcher->forward([
            'controller' => "tasks",
            'action' => 'mytasks',
            'params' => [$userid],
        ]);

    }

    /**
     * Saves a auction edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }

        $auctionId = $this->request->getPost("auctionId");
        $auction = Auctions::findFirstByauctionId($auctionId);

        if (!$auction) {
            $this->flash->error("Такого тендера не существует");

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }

        if($this->session->get("taskId")!='') {
            $taskId = $this->session->get("taskId");
            $this->session->remove("taskId");
        }


        $auction->setTaskid($taskId);

        $auction->setDatestart($this->request->getPost("dateStart"));
        $auction->setDateend($this->request->getPost("dateEnd"));
        

        if (!$auction->save()) {

            foreach ($auction->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'edit',
                'params' => [$auction->getAuctionid()]
            ]);

            return;
        }

        $this->flash->success("Тендер был отредактирован успешно");

        $this->dispatcher->forward([
            'controller' => "auctions",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a auction
     *
     * @param string $auctionId
     */
    public function deleteAction($auctionId)
    {
        $auction = Auctions::findFirstByauctionId($auctionId);
        if (!$auction) {
            $this->flash->error("auction was not found");

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }

        if (!$auction->delete()) {

            foreach ($auction->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("auction was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "auctions",
            'action' => "index"
        ]);
    }

    public function showAction($taskId){

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
        $task=Tasks::find($auction->getTaskId());
        $task=$task->getFirst();
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

        $this->persistent->parameters = null;

        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Offers', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "offerId";
        $auctionid=$auction->getAuctionId();
        $offers = Offers::find("auctionId=$auctionid");
        if (count($offers) == 0) {
            $this->flash->notice("На ваш тендер ещё никто не ответил");
        }

        $paginator = new Paginator([
            'data' => $offers,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    public function viewingAction($auctionId)
    {
            $auction=Auctions::find($auctionId);
            $auction=$auction->getFirst();
            $this->view->setVar('auction',$auction);
        if (!$auction) {
            $this->flash->error("Такого тендера не существует");

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }
            $task=Tasks::find($auction->getTaskId());
            $task=$task->getFirst();
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
    }

    public function choiceAction($offerId)
    {
        $offer=Offers::find("offerId=$offerId");
        $offer=$offer->getFirst();

        if (!$offer) {
            $this->flash->error("offer does not exist ");

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }
        $auctionId=$offer->getAuctionId();
        $auction=Auctions::find("auctionId=$auctionId");
        $auction=$auction->getFirst();
        if (!$auction) {
            $this->flash->error("auction does not exist ");

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'show',
                'params' => [$auctionId]
            ]);

            return;
        }
        $auction->setSelectedOffer($offerId);
        $task = $auction->Tasks;
        $task->setStatus(1);

        if (!$task->save()) {

            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'show',
                'params' => [$auctionId]
            ]);

            return;
        }

        if (!$auction->save()) {

            foreach ($auction->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index',
                'params' => [$task->getTaskId()]
            ]);

            return;
        }

        $this->flash->success("Исполнитель был избран");

        $this->dispatcher->forward([
            'controller' => "coordination",
            'action' => 'index',
            'params' => [$task->getTaskId()]
        ]);

    }
}
