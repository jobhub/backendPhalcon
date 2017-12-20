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
            $this->flash->notice("Не найдено ни одной записи");
        }

        $paginator = new Paginator([
            'data' => $logs,
            'limit'=> 30,
            'page' => $numberPage
        ]);

        $users = Users::find();
        $this->view->setVar('users',$users);

        $this->view->page = $paginator->getPaginate();
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
            $this->flash->error("Запись не найдена");

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

        $this->flash->success("Запись успешно удалена");

        $this->dispatcher->forward([
            'controller' => "logs",
            'action' => "index"
        ]);
    }

}
