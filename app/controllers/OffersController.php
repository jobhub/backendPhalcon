<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class OffersController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Предложения');
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

        $offers = Offers::find($parameters);
        if (count($offers) == 0) {
            $this->flash->notice("The search did not find any offers");
        }

        $paginator = new Paginator([
            'data' => $offers,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Searches for offers
     */
    public function searchAction()
    {
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

        $offers = Offers::find($parameters);
        if (count($offers) == 0) {
            $this->flash->notice("The search did not find any offers");

            $this->dispatcher->forward([
                "controller" => "offers",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $offers,
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
        $this->session->set("auctionId",$auction->getAuctionId());

    }

    /**
     * Edits a offer
     *
     * @param string $offerId
     */
    public function editAction($offerId)
    {
        if (!$this->request->isPost()) {

            $offer = Offers::findFirstByofferId($offerId);
            if (!$offer) {
                $this->flash->error("offer was not found");

                $this->dispatcher->forward([
                    'controller' => "offers",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->offerId = $offer->getOfferid();

            $this->tag->setDefault("offerId", $offer->getOfferid());
            $this->tag->setDefault("userId", $offer->getUserid());
            $this->tag->setDefault("deadline", $offer->getDeadline());
            $this->tag->setDefault("description", $offer->getDescription());
            $this->tag->setDefault("price", $offer->getPrice());
            
        }
    }

    /**
     * Creates a new offer
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

        $auth=$this->session->get("auth");
        if($this->session->get("auctionId")!='') {
            $auctionId = $this->session->get("auctionId");
            $this->session->remove("auctionId");
        }

        $offer = new Offers();
        $offer->setAuctionId($auctionId);
        $offer->setUserid($auth['id']);
        $offer->setDeadline($this->request->getPost("deadlineOffer"));
        $offer->setDescription($this->request->getPost("descriptionOffer"));
        $offer->setPrice($this->request->getPost("priceOffer"));
        

        if (!$offer->save()) {
            foreach ($offer->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'index'
            ]);

            return;
        }

        $this->flash->success("offer was created successfully");

        $this->dispatcher->forward([
            'controller' => "auctions",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a offer edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'index'
            ]);

            return;
        }

        $offerId = $this->request->getPost("offerId");
        $offer = Offers::findFirstByofferId($offerId);

        if (!$offer) {
            $this->flash->error("offer does not exist " . $offerId);

            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'index'
            ]);

            return;
        }

        $offer->setOfferid($this->request->getPost("offerId"));
        $offer->setUserid($this->request->getPost("userId"));
        $offer->setDeadline($this->request->getPost("deadline"));
        $offer->setDescription($this->request->getPost("description"));
        $offer->setPrice($this->request->getPost("price"));
        

        if (!$offer->save()) {

            foreach ($offer->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'edit',
                'params' => [$offer->getOfferid()]
            ]);

            return;
        }

        $this->flash->success("offer was updated successfully");

        $this->dispatcher->forward([
            'controller' => "offers",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a offer
     *
     * @param string $offerId
     */
    public function deleteAction($offerId)
    {
        $offer = Offers::findFirstByofferId($offerId);
        if (!$offer) {
            $this->flash->error("offer was not found");

            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'index'
            ]);

            return;
        }

        if (!$offer->delete()) {

            foreach ($offer->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("offer was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "offers",
            'action' => "index"
        ]);
    }

}
