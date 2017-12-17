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
            $this->flash->notice("The search did not find any auctions");
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
            $this->flash->notice("The search did not find any auctions");

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
        $this->view->setVar("task",$task);

        $this->session->set("taskId",$task->getTaskId());

        $categories=Categories::find();
        $this->view->setVar("categories", $categories);
       // $this->tag->setDefault()
        $this->tag->setDefault("name", $task->getName());
        $this->tag->setDefault("categoryId", $task->getCategoryid());
        $this->tag->setDefault("description", $task->getDescription());
        $this->tag->setDefault("address", $task->getaddress());
        $this->tag->setDefault("deadline", $task->getDeadline());
        $this->tag->setDefault("price", $task->getPrice());

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
                $this->flash->error("auction was not found");

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

        $this->flash->success("auction was created successfully");

        $this->dispatcher->forward([
            'controller' => "auctions",
            'action' => 'index'
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
            $this->flash->error("auction does not exist " . $auctionId);

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

        $this->flash->success("auction was updated successfully");

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

    public function enterAction(){



    }

    public function viewingAction($auctionId)
    {
            $auction=Auctions::find($auctionId);
            $auction=$auction->getFirst();
        if (!$auction) {
            $this->flash->error("auction does not exist " . $auctionId);

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

}
