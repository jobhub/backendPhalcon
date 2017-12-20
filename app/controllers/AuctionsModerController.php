<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class AuctionsModerController extends ControllerBase
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
            $this->flash->notice("Не найдено ни одного тендера");
        }

        $tasks = Tasks::find();
        $this->view->setVar('tasks',$tasks);

        $offers = Offers::find();
        $this->view->setVar('offers',$offers);

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
    public function newAction()
    {
        $tasks = Tasks::find();
        $this->view->setVar('tasks',$tasks);
        $offers = Offers::find();
        $this->view->setVar('offers',$offers);
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
                    'controller' => "auctionsModer",
                    'action' => 'index'
                ]);

                return;
            }

            $tasks = Tasks::find();
            $this->view->setVar('tasks',$tasks);
            $offers = Offers::find();
            $this->view->setVar('offers',$offers);

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
            foreach($_POST as $key=>$value){
                unset($_POST[$key]);
            }

            $this->dispatcher->forward([
                'controller' => "auctionsModer",
                'action' => 'index'
            ]);



            return;
        }

        $auction = new Auctions();
        $auction->setAuctionid($this->request->getPost("auctionId"));
        $auction->setTaskid($this->request->getPost("taskId"));
        $auction->setSelectedoffer($this->request->getPost("selectedOffer"));
        $auction->setDatestart($this->request->getPost("dateStart"));
        $auction->setDateend($this->request->getPost("dateEnd"));
        

        if (!$auction->save()) {
            foreach ($auction->getMessages() as $message) {
                $this->flash->error($message);
            }



            $this->dispatcher->forward([
                'controller' => "auctionsModer",
                'action' => 'new'
            ]);

            return;
        }

        foreach($_POST as $key=>$value){
            unset($_POST[$key]);
        }

        $this->flash->success("Тендер был создан");

        $this->dispatcher->forward([
            'controller' => "auctionsModer",
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
                'controller' => "auctionsModer",
                'action' => 'index'
            ]);

            return;
        }

        $auctionId = $this->request->getPost("auctionId");
        $auction = Auctions::findFirstByauctionId($auctionId);

        if (!$auction) {
            $this->flash->error("Тендер с ID " . $auctionId .' не существует');

            foreach($_POST as $key=>$value){
                unset($_POST[$key]);
            }

            $this->dispatcher->forward([
                'controller' => "auctionsModer",
                'action' => 'index'
            ]);

            return;
        }

        $auction->setAuctionid($this->request->getPost("auctionId"));
        $auction->setTaskid($this->request->getPost("taskId"));
        $auction->setSelectedoffer($this->request->getPost("selectedOffer"));
        $auction->setDatestart($this->request->getPost("dateStart"));
        $auction->setDateend($this->request->getPost("dateEnd"));
        

        if (!$auction->save()) {

            foreach ($auction->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "auctionsModer",
                'action' => 'edit',
                'params' => [$auction->getAuctionid()]
            ]);

            return;
        }

        foreach($_POST as $key=>$value){
            unset($_POST[$key]);
        }

        $this->flash->success("Тендер был изменен");

        $this->dispatcher->forward([
            'controller' => "auctionsModer",
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
            $this->flash->error("Тендер не найден");

            $this->dispatcher->forward([
                'controller' => "auctionsModer",
                'action' => 'index'
            ]);

            return;
        }

        if (!$auction->delete()) {

            foreach ($auction->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "auctionsModer",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("Тендер был успешно удален");

        $this->dispatcher->forward([
            'controller' => "auctionsModer",
            'action' => "index"
        ]);
    }

}
