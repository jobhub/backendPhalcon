<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class UserInfoController extends ControllerBase
{

    public function initialize()
    {
        $this->tag->setTitle('Welcome');
        parent::initialize();
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
    }

    /**
     * Searches for userinfo
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'UserInfo', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "firstname";

        $user_info = UserInfo::find($parameters);
        if (count($user_info) == 0) {
            $this->flash->notice("The search did not find any userinfo");

            $this->dispatcher->forward([
                "controller" => "userinfo",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $user_info,
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
     * @param string $firstname
     */
    public function editAction($firstname)
    {
        if (!$this->request->isPost()) {

            $user_info = UserInfo::findFirstByfirstname($firstname);
            if (!$user_info) {
                $this->flash->error("userinfo was not found");

                $this->dispatcher->forward([
                    'controller' => "userinfo",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->firstname = $user_info->firstname;

            $this->tag->setDefault("firstname", $user_info->firstname);
            $this->tag->setDefault("lastname", $user_info->lastname);
            $this->tag->setDefault("birthday", $user_info->birthday);
            $this->tag->setDefault("male", $user_info->male);
            $this->tag->setDefault("address", $user_info->address);
            $this->tag->setDefault("about", $user_info->about);
            $this->tag->setDefault("executor", $user_info->executor);
            $this->tag->setDefault("user_id", $user_info->user_id);
            
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

        $user_info = new UserInfo();
        $user_info->Firstname = $this->request->getPost("firstname");
        $user_info->Lastname = $this->request->getPost("lastname");
        $user_info->Birthday = $this->request->getPost("birthday");
        $user_info->Male = $this->request->getPost("male");
        $user_info->Address = $this->request->getPost("address");
        $user_info->About = $this->request->getPost("about");
        $user_info->Executor = $this->request->getPost("executor");
        $user_info->User_id = $this->request->getPost("user_id");
        

        if (!$user_info->save()) {
            foreach ($user_info->getMessages() as $message) {
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

        $firstname = $this->request->getPost("firstname");
        $user_info = UserInfo::findFirstByfirstname($firstname);

        if (!$user_info) {
            $this->flash->error("userinfo does not exist " . $firstname);

            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'index'
            ]);

            return;
        }

        $user_info->Firstname = $this->request->getPost("firstname");
        $user_info->Lastname = $this->request->getPost("lastname");
        $user_info->Birthday = $this->request->getPost("birthday");
        $user_info->Male = $this->request->getPost("male");
        $user_info->Address = $this->request->getPost("address");
        $user_info->About = $this->request->getPost("about");
        $user_info->Executor = $this->request->getPost("executor");
        $user_info->User_id = $this->request->getPost("user_id");
        

        if (!$user_info->save()) {

            foreach ($user_info->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'edit',
                'params' => [$user_info->firstname]
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
     * @param string $firstname
     */
    public function deleteAction($firstname)
    {
        $user_info = UserInfo::findFirstByfirstname($firstname);
        if (!$user_info) {
            $this->flash->error("userinfo was not found");

            $this->dispatcher->forward([
                'controller' => "userinfo",
                'action' => 'index'
            ]);

            return;
        }

        if (!$user_info->delete()) {

            foreach ($user_info->getMessages() as $message) {
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
