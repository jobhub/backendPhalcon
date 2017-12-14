<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class LogsController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Логи');
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
            $query = Criteria::fromInput($this->di, 'Logs', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "logId";

        $logs = Logs::find($parameters);
        if (count($logs) == 0) {
            $this->flash->notice("The search did not find any logs");

            $this->dispatcher->forward([
                "controller" => "logs",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $logs,
            'limit'=> 30,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }


    /**
     * Searches for logs
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Logs', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "logId";

        $logs = Logs::find($parameters);
        if (count($logs) == 0) {
            $this->flash->notice("The search did not find any logs");

            $this->dispatcher->forward([
                "controller" => "logs",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $logs,
            'limit'=> 30,
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
     * Edits a log
     *
     * @param string $logId
     */
    public function editAction($logId)
    {
        if (!$this->request->isPost()) {

            $log = Logs::findFirstBylogId($logId);
            if (!$log) {
                $this->flash->error("log was not found");

                $this->dispatcher->forward([
                    'controller' => "logs",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->logId = $log->getLogid();

            $this->tag->setDefault("logId", $log->getLogid());
            $this->tag->setDefault("userId", $log->getUserid());
            $this->tag->setDefault("controller", $log->getController());
            $this->tag->setDefault("action", $log->getAction());
            $this->tag->setDefault("date", $log->getDate());
            
        }
    }

    /**
     * Creates a new log
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "logs",
                'action' => 'index'
            ]);

            return;
        }

        $log = new Logs();
        $log->setUserid($this->request->getPost("userId"));
        $log->setController($this->request->getPost("controller"));
        $log->setAction($this->request->getPost("action"));
        $log->setDate($this->request->getPost("date"));
        

        if (!$log->save()) {
            foreach ($log->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "logs",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("log was created successfully");

        $this->dispatcher->forward([
            'controller' => "logs",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a log edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "logs",
                'action' => 'index'
            ]);

            return;
        }

        $logId = $this->request->getPost("logId");
        $log = Logs::findFirstBylogId($logId);

        if (!$log) {
            $this->flash->error("log does not exist " . $logId);

            $this->dispatcher->forward([
                'controller' => "logs",
                'action' => 'index'
            ]);

            return;
        }

        $log->setUserid($this->request->getPost("userId"));
        $log->setController($this->request->getPost("controller"));
        $log->setAction($this->request->getPost("action"));
        $log->setDate($this->request->getPost("date"));
        

        if (!$log->save()) {

            foreach ($log->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "logs",
                'action' => 'edit',
                'params' => [$log->getLogid()]
            ]);

            return;
        }

        $this->flash->success("log was updated successfully");

        $this->dispatcher->forward([
            'controller' => "logs",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a log
     *
     * @param string $logId
     */
    public function deleteAction($logId)
    {
        $log = Logs::findFirstBylogId($logId);
        if (!$log) {
            $this->flash->error("log was not found");

            $this->dispatcher->forward([
                'controller' => "logs",
                'action' => 'index'
            ]);

            return;
        }

        if (!$log->delete()) {

            foreach ($log->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "logs",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("log was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "logs",
            'action' => "index"
        ]);
    }

}
