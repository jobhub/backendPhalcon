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
            $this->flash->notice("Предложение не найдено");
        }

        $users = Users::find();


        //$this->view->$users = $users;
        $this->view->setVar("users", $users);

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
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $this->view->setVar("userId", $userId);

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
        $parameters[0]="userId=$userId";
        $parameters["order"] = "offerId";

        $offers = Offers::find($parameters);
        if (count($offers) == 0) {
            $this->flash->notice("Предложение не было найдено");

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
        if($this->session->get('auctionId')!=null) {
            $auctionId = $this->session->get('auctionId');
            $auction = Auctions::findFirst("auctionId=$auctionId");
            $this->session->remove('auctionId');
        }
        if (!$auction) {
            $this->flash->error("Такого тендера не существует ");

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);
            return;
        }

        $auth=$this->session->get('auth');
        $userid=$auth['id'];
       /* $executor=Userinfo::findFirst("userId=$userid")->getExecutor();
        if($executor==0)
        {
            $this->dispatcher->forward([
                "controller" => "auctions",
                "action" => "index"
            ]);
            $this->flash->error("Вы не являетесь исполнителем ");
            return;
        }*/
        $taskId=$auction->getTaskId();
        $task=Tasks::findFirst("taskId=$taskId");
        if($userid==$task->getUserId())
        {
            $this->dispatcher->forward([
                "controller" => "auctions",
                "action" => "index"
            ]);
            $this->flash->error("Нельзя вступить в собственный тендер");
            return;
        }
        $offers=Offers::find("userId=$userid and auctionId=$auctionId");
        if(count($offers) != 0)
        {
            $this->dispatcher->forward([
                "controller" => "offers",
                "action" => "myoffers",
                'params' => [$userid]
            ]);
            $this->flash->error("Вы уже оставляли предложение для этого тендера ");
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
                $this->flash->error("Предложение не найдено");

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

    public function editingAction($offerId)
    {
        $auth=$this->session->get("auth");
        $offerUserId=Offers::findFirst("offerId=$offerId");
        if($offerUserId == false)
        {
            $this->flash->notice("Предложение не найдено");

            $this->dispatcher->forward([
                "controller" => "offers",
                "action" => "myoffers",
                'params' => [$auth['id']],

            ]);

            return;
        }
        $offerUserId=$offerUserId->getUserId();
        if($auth['id']===$offerUserId)
        {
        if (!$this->request->isPost()) {

            $offer = Offers::findFirstByofferId($offerId);
            if (!$offer) {
                $this->flash->error("Предложение не найдено");

                $this->dispatcher->forward([
                    'controller' => "offers",
                    'action' => 'index'
                ]);

                return;
            }

            $auctions=Auctions::find("selectedOffer=$offerId");
            if(count($auctions)!=0)
            {
                $this->flash->error("Нельзя редактировать предложение, которые была выбрано как исполняемое в тендере");

                $this->dispatcher->forward([
                    'controller' => "offers",
                    'action' => 'myoffers'
                ]);

                return;
            }

            $this->view->offerId = $offer->getOfferid();
            $this->tag->setDefault("offerId", $offer->getOfferid());
            $this->tag->setDefault("deadline", $offer->getDeadline());
            $this->tag->setDefault("description", $offer->getDescription());
            $this->tag->setDefault("price", $offer->getPrice());
        }
        else
        {
            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'myoffers',
                'params' => [$auth['id']],
            ]);
        }

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
            $userid=$auth['id'];
            $offers=Offers::find("userId=$userid and auctionId=$auctionId");
            if(count($offers) != 0) {
                $this->flash->error("Вы уже оставляли предложение для этого тендера ");
                $this->dispatcher->forward([
                    "controller" => "auctions",
                    "action" => "index"
                ]);
            }
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
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }

        $this->flash->success("Предложение добавлено");

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
            $this->flash->error("Предложение не существует ");

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

        $this->flash->success("Предложение изменено успешно");

        $this->dispatcher->forward([
            'controller' => "offers",
            'action' => 'index'
        ]);
    }


    public function savingAction()
    {
        $auth=$this->session->get("auth");
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
            $this->flash->error("Предложение не существует ");

            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'index'
            ]);

            return;
        }

        $offer->setOfferid($this->request->getPost("offerId"));
        $offer->setUserid($auth['id']);
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

        $this->flash->success("Предложение обновлено успешно");

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
            $this->flash->error("Предложение не найдено");

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

        $this->flash->success("Предложение удалено успешно");

        $this->dispatcher->forward([
            'controller' => "offers",
            'action' => "index"
        ]);
    }

    public function deletingAction($offerId)
    {
        $auth=$this->session->get("auth");
        $offerUserId=Offers::findFirst("offerId=$offerId");
        if($offerUserId == false)
        {
            $this->flash->notice("Предложение не найдено");

            $this->dispatcher->forward([
                "controller" => "offers",
                "action" => "myoffers",
                'params' => [$auth['id']],

            ]);

            return;
        }
        $offerUserId=$offerUserId->getUserId();
        if($auth['id']===$offerUserId) {
            $offer = Offers::findFirstByofferId($offerId);
            if (!$offer) {
                $this->flash->error("Предложение не найдено");

                $this->dispatcher->forward([
                    'controller' => "offers",
                    'action' => 'index'
                ]);

                return;
            }

            $auctions=Auctions::find("selectedOffer=$offerId");
            if(count($auctions)!=0)
            {
                $this->flash->error("Нельзя удалить предложение, которые была выбрано как исполняемое в тендере");

                $this->dispatcher->forward([
                    'controller' => "offers",
                    'action' => 'myoffers'
                ]);

                return;
            }
            if (!$offer->delete()) {

                foreach ($offer->getMessages() as $message) {
                    $this->flash->error($message);
                }

                $this->dispatcher->forward([
                    'controller' => "offers",
                    'action' => 'myoffers',
                    'params' => [$auth['id']],
                ]);

                return;

            }
        }
            $this->dispatcher->forward([
                'controller' => "offers",
                'action' => 'myoffers',
                'params' => [$auth['id']],
            ]);

    }

    public function myoffersAction($userId)
    {
        $this->persistent->parameters = null;
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $this->view->setVar("userId", $userId);

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
        // $parameters["userId"] = $userId;
        // $parameters["order"] = "taskId";
        $offers = Offers::find("userId=$userId");
        if (count($offers) == 0) {
            $this->flash->notice("Предложений не найдено");
        }
        // $categoryId=$tasks->getCategoryId();
        //   $categories=Categories::findFirst("categoryId=$categoryId");
        //   $this->view->setVar("categories", $categories->getCategoryName());

        $paginator = new Paginator([
            'data' => $offers,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

}
