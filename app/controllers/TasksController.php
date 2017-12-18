<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class TasksController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Задания');
        parent::initialize();
    }
    /**
     * Index action
     */
    public function indexAction()
    {

        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $this->view->setVar("userId", $userId);

    }

    /**
     * Searches for tasks
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Tasks', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "taskId";

        $tasks = Tasks::find($parameters);
        if (count($tasks) == 0) {
            $this->flash->notice("The search did not find any tasks");

            $this->dispatcher->forward([
                "controller" => "tasks",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $tasks,
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
        $categories=Categories::find();
        $this->view->setVar("categories", $categories);

    }

    /**
     * Edits a task
     *
     * @param string $taskId
     */
    public function editAction($taskId)
    {
        $auth=$this->session->get("auth");
        $taskUserId=Tasks::findFirst("taskId=$taskId");
        if($taskUserId == false)
        {
            $this->flash->notice("The search did not find any tasks");

            $this->dispatcher->forward([
                "controller" => "tasks",
                "action" => "index"
            ]);

            return;
        }
        $taskUserId=$taskUserId->getUserId();
        if($auth['id']===$taskUserId)
        {
        if (!$this->request->isPost()) {

            $task = Tasks::findFirstBytaskId($taskId);
            if (!$task) {
                $this->flash->error("task was not found");

                $this->dispatcher->forward([
                    'controller' => "tasks",
                    'action' => 'index'
                ]);

                return;
            }
            $categories=Categories::find();
            $this->view->setVar("categories", $categories);

            $this->view->taskId = $task->getTaskid();

            $this->tag->setDefault("taskId", $task->getTaskid());
            $this->tag->setDefault("userId", $task->getUserid());
            $this->tag->setDefault("categoryId", $task->getCategoryid());
            $this->tag->setDefault("description", $task->getDescription());
            $this->tag->setDefault("address", $task->getaddress());
            $this->tag->setDefault("deadline", $task->getDeadline());
            $this->tag->setDefault("price", $task->getPrice());

            $this->session->set("taskId", $task->getTaskid());
            }
        else
        {
            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'index'
            ]);
        }
        }
    }

    /**
     * Creates a new task
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'index'
            ]);

            return;
        }


        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $task = new Tasks();
       // $task->setTaskid($this->request->getPost("taskId"));
        $task->setUserid($userId);
        $task->setCategoryid($this->request->getPost("categoryId"));
        $task->setName($this->request->getPost("name"));
        $task->setDescription($this->request->getPost("description"));
        $task->setaddress($this->request->getPost("address"));
        $task->setDeadline($this->request->getPost("deadline"));
        $task->setPrice($this->request->getPost("price"));
        

        if (!$task->save()) {
            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("task was created successfully");

        $this->dispatcher->forward([
            'controller' => "auctions",
            'action' => 'new',
            'params' => [$task->getTaskId()],
        ]);
    }

    /**
     * Saves a task edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'index'
            ]);

            return;
        }
        if($this->session->get("taskId")!='') {
            $taskId = $this->session->get("taskId");
            $this->session->remove("taskId");
        }
        $task = Tasks::findFirstBytaskId($taskId);

        if (!$task) {
            $this->flash->error("task does not exist " . $taskId);

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'index'
            ]);

            return;
        }

        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        $task->setTaskid($taskId);
        $task->setUserid($userId);
        $task->setCategoryid($this->request->getPost("categoryId"));
        $task->setDescription($this->request->getPost("description"));
        $task->setaddress($this->request->getPost("address"));
        $task->setDeadline($this->request->getPost("deadline"));
        $task->setPrice($this->request->getPost("price"));
        

        if (!$task->save()) {

            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'edit',
                'params' => [$task->getTaskid()]
            ]);

            return;
        }

        $this->flash->success("task was updated successfully");

        $this->dispatcher->forward([
            'controller' => "tasks",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a task
     *
     * @param string $taskId
     */
    public function deleteAction($taskId)
    {
        $task = Tasks::findFirstBytaskId($taskId);
        if (!$task) {
            $this->flash->error("task was not found");

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'index'
            ]);

            return;
        }

        if (!$task->delete()) {

            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("task was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "tasks",
            'action' => "index"
        ]);
    }

    public function mytasksAction($userId)
    {
        $this->persistent->parameters = null;



        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $this->view->setVar("userId", $userId);

        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Tasks', $_POST);
            $query->andWhere();
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        // $parameters["userId"] = $userId;
         $parameters["order"] = "taskId";
        $tasks = Tasks::find("userId=$userId");
        if (count($tasks) == 0) {
            $this->flash->notice("The search did not find any tasks");
        }
        // $categoryId=$tasks->getCategoryId();
        //   $categories=Categories::findFirst("categoryId=$categoryId");
        //   $this->view->setVar("categories", $categories->getCategoryName());

        $paginator = new Paginator([
            'data' => $tasks,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }


    public function doingtasksAction($userId)
    {
        $this->persistent->parameters = null;

        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $this->view->setVar("userId", $userId);

        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Tasks', $_POST);
            $query->andWhere();
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
            $this->flash->notice("The search did not find any offers");
        }
        $tasks=$offers->auctions;
        $tasks=$offers->auctions->tasks;

        // $categoryId=$tasks->getCategoryId();
        //   $categories=Categories::findFirst("categoryId=$categoryId");
        //   $this->view->setVar("categories", $categories->getCategoryName());

        $paginator = new Paginator([
            'data' => $tasks,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

}
