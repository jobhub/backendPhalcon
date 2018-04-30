<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;


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
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $this->view->setVar("userId", $userId);

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
        $parameters[0] = "userId=$userId";
        $parameters["order"] = "taskId";

        $tasks = Tasks::find($parameters);
        if (count($tasks) == 0) {
            $this->flash->notice("Такого тендера не существует");

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
        $this->assets->addJs("https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js",false);
        $this->assets->addJs("http://api-maps.yandex.ru/2.1/?lang=ru_RU",false);
        $this->assets->addJs("/public/js/map.js",true);
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
            $this->flash->notice("Такого задания не существует");

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
                $this->flash->error("Такого задания не существует");

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

    public function editingAction($taskId)
    {
        $auth=$this->session->get("auth");
        $taskUserId=Tasks::findFirst("taskId=$taskId");
        if($taskUserId == false)
        {
            $this->flash->notice("Такого задания не существует");

            $this->dispatcher->forward([
                "controller" => "tasks",
                "action" => "index"
            ]);

            return;
        }
        $auction = Auctions::findFirst("taskId=$taskId");
        $auctionId=$auction->getAuctionId();
        $offers=Offers::find("auctionId=$auctionId");

        if(count($offers)>0)
        {
            $this->flash->error("Нельзя редактировать тендер, на который откликнулись люди");

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'mytasks'
            ]);

            return;
        }
        $taskUserId=$taskUserId->getUserId();
        if($auth['id']===$taskUserId)
        {
            if (!$this->request->isPost()) {

                $task = Tasks::findFirstBytaskId($taskId);
                if (!$task) {
                    $this->flash->error("Такого задания не существует");

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
        $task->setAddress($this->request->getPost("address"));
        $task->setDeadline($this->request->getPost("deadline"));
        $task->setPrice($this->request->getPost("price"));
        $coords=$this->request->getPost("coord");
        $latitude=strstr($coords,',',true);
        $longitude=substr(strstr($coords,','),1);
        $task->setLatitude($latitude);
        $task->setLongitude($longitude);

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

        $this->flash->success("задание созданно успешно");

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
            $this->flash->error("Такого задания несуществует " . $taskId);

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
        $task->setAddress($this->request->getPost("address"));
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

        $this->flash->success("Задание изменено успешно");

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
            $this->flash->error("Такого задания не существует");

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'index'
            ]);

            return;
        }
        $auction = Auctions::findFirst("taskId=$taskId");
        $auctionId=$auction->getAuctionId();
        $offers=Offers::find("auctionId=$auctionId");

        if(count($offers)>0)
        {
            $this->flash->error("Нельзя удалить тендер, на который откликнулись люди");

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'mytasks'
            ]);

            return;
        }


        if (!$auction->delete() and !$task->delete()) {

            foreach ($task->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tasks",
                'action' => 'mytasks'
            ]);

            return;
        }

        $this->flash->success("Задание удалено");

        $this->dispatcher->forward([
            'controller' => "tasks",
            'action' => "mytasks"
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
            $this->flash->notice("Задания не найдены");
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

      //  $query = $this->modelsManager->createQuery('SELECT Tasks.taskId, Tasks.categoryId, Tasks.description, Tasks.address, Task.deadline, Tasks.price, Tasks.status FROM Users, Tasks, Auctions, Offers WHERE Users.userId=:userid: and Tasks.userId=Users.userId AND Auctions.taskId=Tasks.taskId AND Offers.auctionId=Auctions.auctionId AND Auctions.selectedOffer=Offers.offerId');
        $query = $this->modelsManager->createQuery('SELECT * FROM Users, Tasks, Auctions, Offers WHERE Users.userId=:userid: and Tasks.userId=Users.userId AND Auctions.taskId=Tasks.taskId AND Offers.auctionId=Auctions.auctionId AND Auctions.selectedOffer=Offers.offerId  AND Tasks.Status = :status:');
        
      $tasks  = $query->execute(
            [
                'userid' => "$userId",
                'status' => "Выполняется",
            ]
        );
        $this->view->setVar("task", $tasks);


        /*$t=null;
       foreach ($tasks as $task)
       {
           $s=$task->taskId;
           $t=(Tasks::find("taskId=$s"));
       }



       $taskIds=$tasks->rows;
       $tasks=Tasks::find("userId=$userId");
       $auctions=$tasks->auctions->offers;
       $offers=$auctions->offers;
*/
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

        // $parameters["userId"] = $userId;
        // $parameters["order"] = "taskId";
       /* $offers = Offers::find("userId=$userId");
        if (count($offers) == 0) {
            $this->flash->notice("Задание не найдено");
        }*/

      //  $tasks=$offers->auctions;
      //  $tasks=$offers->auctions->tasks;

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

    public function workingtasksAction($userId)
    {

        $this->persistent->parameters = null;

        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $this->view->setVar("userId", $userId);

        //  $query = $this->modelsManager->createQuery('SELECT Tasks.taskId, Tasks.categoryId, Tasks.description, Tasks.address, Task.deadline, Tasks.price, Tasks.status FROM Users, Tasks, Auctions, Offers WHERE Users.userId=:userid: and Tasks.userId=Users.userId AND Auctions.taskId=Tasks.taskId AND Offers.auctionId=Auctions.auctionId AND Auctions.selectedOffer=Offers.offerId');

        $query = $this->modelsManager->createQuery('SELECT * FROM Tasks, Auctions, Offers WHERE Offers.userId=:userid: AND Auctions.selectedOffer=Offers.offerId AND Tasks.taskId=Auctions.taskId');

        $tasks  = $query->execute(
            [
                'userid' => $userId,
            ]
        );

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



        $paginator = new Paginator([
            'data' => $tasks,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

}
