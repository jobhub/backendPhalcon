<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class UserinfoController extends ControllerBase
{


     public function initialize()
     {
         $this->tag->setTitle('');
         parent::initialize();
     }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
        $user_id=$_SESSION['auth']['id'];
        if($user_id)
        {
          $this->dispatcher->forward([
              'controller' => "userinfo",
              'action' => 'edit',
              'params' => [$user_id]
          ]);
        }
        else {
          $this->dispatcher->forward([
              'controller' => "index",
              'action' => 'index']);
        }
    }

    /**
     * Searches for userinfo
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Userinfo', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "userId";

        $userinfo = Userinfo::find($parameters);
        if (count($userinfo) == 0) {
            $this->flash->notice("The search did not find any userinfo");

            $this->dispatcher->forward([
                "controller" => "userinfo",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $userinfo,
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

    }

    /**
     * Edits a userinfo
     *
     * @param string $userId
     */
    public function editAction($userId)
    {
        if (!$this->request->isPost()) {

            $userinfo = Userinfo::findFirstByuserId($userId);
            if (!$userinfo) {
                $this->flash->error("userinfo was not found");

                $this->dispatcher->forward([
                    'controller' => "userinfo",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->userId = $userinfo->userId;

            $this->tag->setDefault("userId", $userinfo->userId);
            $this->tag->setDefault("firstname", $userinfo->firstname);
            $this->tag->setDefault("patronymic", $userinfo->patronymic);
            $this->tag->setDefault("lastname", $userinfo->lastname);
            $this->tag->setDefault("birthday", $userinfo->birthday);
            $this->tag->setDefault("male", $userinfo->male);
            $this->tag->setDefault("address", $userinfo->address);
            $this->tag->setDefault("about", $userinfo->about);
            $this->tag->setDefault("executor",$userinfo->executor);
        }
    }

    /**
     * Creates a new userinfo
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'index'
            ]);

            return;
        }

        $userinfo = new Userinfo();
        $userinfo->Userid = $this->request->getPost("userId");
        $userinfo->Firstname = $this->request->getPost("firstname");
        $userinfo->Patronymic = $this->request->getPost("patronymic");
        $userinfo->Lastname = $this->request->getPost("lastname");
        $userinfo->Birthday = $this->request->getPost("birthday");
        $userinfo->Male = $this->request->getPost("male");
        $userinfo->Address = $this->request->getPost("address");
        $userinfo->About = $this->request->getPost("about");
        $userinfo->Executor = $this->request->getPost("executor");


        if (!$userinfo->save()) {
            foreach ($userinfo->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("userinfo was created successfully");

        $this->dispatcher->forward([
            'controller' => "userinfo",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a userinfo edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'index'
            ]);

            return;
        }
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $userinfo = Userinfo::findFirstByuserId($userId);

        if (!$userinfo) {
            $this->flash->error("userinfo does not exist " . $userId);

            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'index'
            ]);

            return;
        }

        $userinfo->setUserid($auth['id']);
        $userinfo->setFirstname($this->request->getPost("firstname"));
        $userinfo->setPatronymic($this->request->getPost("patronymic"));
        $userinfo->setLastname($this->request->getPost("lastname"));
        $userinfo->setBirthday($this->request->getPost("birthday"));
        if($this->request->getPost("male")==="1")
            $userinfo->setMale("1");
        else
            $userinfo->setMale("0");
        //$userinfo->Male = $this->request->getPost("male");
        $userinfo->setAddress($this->request->getPost("address"));
        $userinfo->setAbout($this->request->getPost("about"));
        $userinfo->setExecutor($this->request->getPost("executor"));

        if (!$userinfo->save()) {

            foreach ($userinfo->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'edit',
                'params' => [$userinfo->userId]
            ]);

            return;
        }

        $this->flash->success("userinfo was updated successfully");

        $this->dispatcher->forward([
            'controller' => "userinfo",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a userinfo
     *
     * @param string $userId
     */
    public function deleteAction($userId)
    {
        $userinfo = Userinfo::findFirstByuserId($userId);
        if (!$userinfo) {
            $this->flash->error("userinfo was not found");

            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'index'
            ]);

            return;
        }

        if (!$userinfo->delete()) {

            foreach ($userinfo->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("userinfo was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "userinfo",
            'action' => "index"
        ]);
    }

}
