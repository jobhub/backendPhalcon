<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class TasksModerController extends ControllerBase
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
            $this->flash->notice("Не найдено ни одного задания");
        }

        $categories = Categories::find();

        $this->view->setVar('categories',$categories);

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
        $categories = Categories::find();

        $this->view->setVar('categories',$categories);
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
                $this->flash->error("Задание не найдено");

                $this->dispatcher->forward([
                    'controller' => "tasksModer",
                    'action' => 'index'
                ]);

                return;
            }

            $categories = Categories::find();

            $this->view->setVar('categories',$categories);

            $this->view->taskId = $task->getTaskid();

            $this->tag->setDefault("taskId", $task->getTaskid());
            $this->tag->setDefault("name", $task->getName());
            $this->tag->setDefault("userId", $task->getUserid());
            $this->tag->setDefault("categoryId", $task->getCategoryid());
            $this->tag->setDefault("description", $task->getDescription());
            $this->tag->setDefault("address", $task->getAddress());
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
                'controller' => "tasksModer",
                'action' => 'index'
            ]);

            return;
        }

        $task = new Tasks();
        $task->setTaskid($this->request->getPost("taskId"));
        $task->setName($this->request->getPost("name"));
        $task->setUserid($this->request->getPost("userId"));
        $task->setCategoryid($this->request->getPost("categoryId"));
        $task->setDescription($this->request->getPost("description"));
        $task->setAddress($this->request->getPost("address"));
        $task->setDeadline($this->request->getPost("deadline"));
        $task->setPrice($this->request->getPost("price"));
        

        if (!$task->save()) {
            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tasksModer",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("Задание успешно создано");

        foreach($_POST as $key=>$value){
            unset($_POST[$key]);
        }

        $this->dispatcher->forward([
            'controller' => "tasksModer",
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
                'controller' => "tasksModer",
                'action' => 'index'
            ]);

            return;
        }

        $taskId = $this->request->getPost("taskId");
        $task = Tasks::findFirstBytaskId($taskId);

        if (!$task) {
            $this->flash->error("Задание с ID " . $taskId . " не найдено ");

            $this->dispatcher->forward([
                'controller' => "tasksModer",
                'action' => 'index'
            ]);

            return;
        }

        //$task->setTaskid($this->request->getPost("taskId"));
        $task->setUserid($this->request->getPost("userId"));
        $task->setCategoryid($this->request->getPost("categoryId"));
        $task->setDescription($this->request->getPost("description"));
        $task->setAddress($this->request->getPost("address"));
        $task->setDeadline($this->request->getPost("deadline"));
        $task->setPrice($this->request->getPost("price"));
        

        if (!$task->save()) {

            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tasksModer",
                'action' => 'edit',
                'params' => [$task->getTaskid()]
            ]);

            return;
        }

        $this->flash->success("Задание успешно изменено");

        foreach($_POST as $key=>$value){
            unset($_POST[$key]);
        }

        $this->dispatcher->forward([
            'controller' => "tasksModer",
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
            $this->flash->error("Задание не найдено");

            $this->dispatcher->forward([
                'controller' => "tasksModer",
                'action' => 'index'
            ]);

            return;
        }

        if (!$task->delete()) {

            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tasksModer",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("Задание успешно удалено");

        $this->dispatcher->forward([
            'controller' => "tasksModer",
            'action' => "index"
        ]);
    }

}
