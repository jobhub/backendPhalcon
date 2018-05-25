<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class UsersController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Users');
        parent::initialize();
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
        $numberPage = 1;

        if($this->request->isPost()) {

            $query = Criteria::fromInput($this->di, 'Users', $_POST);
            $query2 = Criteria::fromInput($this->di, 'Userinfo', $_POST);

            $this->persistent->parameters = $query->getParams();
        }
        else{
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "userId";

        //$userinfo = Userinfo::findFirst($parameters);

        $users = Users::find($parameters);
        if (count($users) == 0) {
            $this->flash->notice("Не найдено ни одного пользователя");
        }

        $paginator = new Paginator([
            'data' => $users,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Searches for users
     */
    /*
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Users', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "userId";

        $users = Users::find($parameters);
        if (count($users) == 0) {
            $this->flash->notice("The search did not find any users");

            $this->dispatcher->forward([
                "controller" => "users",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $users,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }
     */
    /**
     * Displays the creation form
     */
    public function newAction()
    {

    }

    /**
     * Edits a user
     *
     * @param string $userId
     */
    public function editAction($userId)
    {
        if (!$this->request->isPost()) {
            $user = Users::findFirstByuserId($userId);
            if (!$user) {
                $this->flash->error("Пользователь не найден");

                $this->dispatcher->forward([
                    'controller' => "users",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->userId = $user->getUserid();

            $this->tag->setDefault("userId", $user->getUserid());
            $this->tag->setDefault("email", $user->getEmail());
            $this->tag->setDefault("phone", $user->getPhone());
            $this->tag->setDefault("role", $user->getRole());

            $userinfo = Userinfo::findFirstByuserId($userId);

            $this->tag->setDefault("userId", $userinfo->getUserId());
            $this->tag->setDefault("firstname", $userinfo->getFirstname());
            $this->tag->setDefault("patronymic", $userinfo->getPatronymic());
            $this->tag->setDefault("lastname", $userinfo->getLastname());
            $this->tag->setDefault("birthday", $userinfo->getBirthday());
            $this->tag->setDefault("male", $userinfo->getMale());
            $this->tag->setDefault("address", $userinfo->getAddress());
            $this->tag->setDefault("about", $userinfo->getAbout());
            $this->tag->setDefault("executor", $userinfo->getExecutor());

            $settings = Settings::findFirstByuserId($userId);

            $this->tag->setDefault("radius", $settings->getSearchRadius());
        }
    }

    /**
     * Creates a new user
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'index'
            ]);

            return;
        }

        $user = new Users();
        $user->setEmail($this->request->getPost("email", "email"));
        $user->setPhone($this->request->getPost("phone"));
        $user->setPassword($this->request->getPost("password"));
        $user->setRole($this->request->getPost("role"));
        

        if (!$user->save()) {
            foreach ($user->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'new'
            ]);

            return;
        }

        $userInfo = new Userinfo();
        $userInfo->setUserId($user->getUserId());
        $userInfo->setFirstname($this->request->getPost('firstname'));
        $userInfo->setLastname($this->request->getPost('lastname'));
        $userInfo->setMale($this->request->getPost('male'));
        $userInfo->setExecutor(0);

        if ($userInfo->save() == false) {

            foreach ($userInfo->getMessages() as $message) {
                $this->flash->error((string) $message);
            }
        }

        $setting = new Settings();
        $setting->setUserId($user->getUserId());

        if ($setting->save() == false) {

            foreach ($setting->getMessages() as $message) {
                $this->flash->error((string) $message);
            }
        }

        $this->flash->success("Пользователь успешно добавлен");

        foreach($_POST as $key=>$value){
            unset($_POST[$key]);
        }

        $this->dispatcher->forward([
            'controller' => "users",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a user edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'index'
            ]);

            return;
        }

        $userId = $this->request->getPost("userId");
        $user = Users::findFirstByuserId($userId);

        if (!$user) {
            $this->flash->error("Пользователя с ID " . $userId. " не существует");

            foreach($_POST as $key=>$value){
                unset($_POST[$key]);
            }

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'index'
            ]);

            return;
        }

        $user->setEmail($this->request->getPost("email"));
        $user->setPhone($this->request->getPost("phone"));
        if($this->request->getPost("password") != "")
            $user->setPassword($this->request->getPost("password"));
        $user->setRole($this->request->getPost("role"));

        $this->db->begin();

        if (!$user->save()) {
            $this->db->rollback();
            foreach ($user->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'edit',
                'params' => [$user->getUserid()]
            ]);

            return;
        }

        $userinfo = UserInfo::findFirstByuserId($userId);

        $userinfo->setFirstname($this->request->getPost("firstname"));
        $userinfo->setLastname($this->request->getPost("lastname"));
        $userinfo->setPatronymic($this->request->getPost("patronymic"));
        $userinfo->setBirthday($this->request->getPost("birthday"));
        $userinfo->setMale($this->request->getPost("male"));
        $userinfo->setAddress($this->request->getPost("address"));
        $userinfo->setAbout($this->request->getPost("about"));
        $userinfo->setExecutor($this->request->getPost("executor"));

        if (!$userinfo->save()) {
            $this->db->rollback();
            foreach ($userinfo->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'edit',
                'params' => [$user->getUserid()]
            ]);

            return;
        }

        $settings = Settings::findFirstByuserId($userId);

        $settings->setSearchRadius($this->request->getPost("radius"));


        if (!$settings->save()) {
            $this->db->rollback();
            foreach ($settings->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'edit',
                'params' => [$user->getUserid()]
            ]);

            return;
        }

        $this->db->commit();

        $this->flash->success("Данные пользователя успешно изменены");

        foreach($_POST as $key=>$value){
            unset($_POST[$key]);
        }

        $this->dispatcher->forward([
            'controller' => "users",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a user
     *
     * @param string $userId
     */
    public function deleteAction($userId)
    {
        $user = Users::findFirstByuserId($userId);
        if (!$user) {
            $this->flash->error("Пользователь не найден");

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'index'
            ]);

            return;
        }

        if (!$user->delete()) {

            foreach ($user->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("Пользователь успешно удален");

        $this->dispatcher->forward([
            'controller' => "users",
            'action' => "index"
        ]);
    }

}
