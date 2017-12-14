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
        $this->persistent->parameters = null;

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
        }

        $paginator = new Paginator([
            'data' => $tasks,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
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

    }

    /**
     * Edits a task
     *
     * @param string $taskId
     */
    public function editAction($taskId)
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

            $this->view->taskId = $task->getTaskid();

            $this->tag->setDefault("taskId", $task->getTaskid());
            $this->tag->setDefault("userId", $task->getUserid());
            $this->tag->setDefault("categoryId", $task->getCategoryid());
            $this->tag->setDefault("description", $task->getDescription());
            $this->tag->setDefault("deadline", $task->getDeadline());
            $this->tag->setDefault("price", $task->getPrice());
            
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

        $task = new Tasks();
        $task->setTaskid($this->request->getPost("taskId"));
        $task->setUserid($this->request->getPost("userId"));
        $task->setCategoryid($this->request->getPost("categoryId"));
        $task->setDescription($this->request->getPost("description"));
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
            'controller' => "tasks",
            'action' => 'index'
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

        $taskId = $this->request->getPost("taskId");
        $task = Tasks::findFirstBytaskId($taskId);

        if (!$task) {
            $this->flash->error("task does not exist " . $taskId);

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'index'
            ]);

            return;
        }

        $task->setTaskid($this->request->getPost("taskId"));
        $task->setUserid($this->request->getPost("userId"));
        $task->setCategoryid($this->request->getPost("categoryId"));
        $task->setDescription($this->request->getPost("description"));
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

}
