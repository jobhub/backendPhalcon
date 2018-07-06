<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class CompaniesController extends ControllerBase
{

    public function initialize()
    {
        $this->tag->setTitle('Аукционы');
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
     * Searches for companies
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Companies', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "companyId";

        $companies = Companies::find($parameters);
        if (count($companies) == 0) {
            $this->flash->notice("The search did not find any companies");

            $this->dispatcher->forward([
                "controller" => "companies",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $companies,
            'limit' => 10,
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
     * Edits a companie
     *
     * @param string $companyId
     */
    public function editAction($companyId)
    {
        if (!$this->request->isPost()) {

            $companie = Companies::findFirstBycompanyId($companyId);
            if (!$companie) {
                $this->flash->error("companie was not found");

                $this->dispatcher->forward([
                    'controller' => "companies",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->companyId = $companie->getCompanyid();

            $this->tag->setDefault("companyId", $companie->getCompanyid());
            $this->tag->setDefault("name", $companie->getName());
            $this->tag->setDefault("fullName", $companie->getFullname());
            $this->tag->setDefault("TIN", $companie->getTin());
            $this->tag->setDefault("region", $companie->getRegion());
            $this->tag->setDefault("userId", $companie->getUserid());

        }
    }

    /**
     * Creates a new companie
     */
    public function createAction()
    {
        /*if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "companies",
                'action' => 'index'
            ]);

            return;
        }*/

        /*$companie = new Companies();
        $companie->setName($this->request->getPost("name"));
        $companie->setFullname($this->request->getPost("fullName"));
        $companie->setTin($this->request->getPost("TIN"));
        $companie->setContactdetails($this->request->getPost("contactDetails"));
        $companie->setRegion($this->request->getPost("region"));
        $companie->setUserid($this->request->getPost("userId"));
        

        if (!$companie->save()) {
            foreach ($companie->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "companies",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("companie was created successfully");*/

        /*$curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://test/phpinfo.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Postman-Token: 46095351-1a2e-4011-b1c7-aaf845d94841"
            ),
            CURLOPT_SSL_VERIFYPEER => false,
        ));

        $result = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $result;*/

        $result = $this->CompaniesAPI->addCompanyAction();

        $result = json_decode($result->getContent());

        if ($result->status == STATUS_OK) {
            $this->flash->success("company was created successfully");
        } else {
            foreach ($result->errors as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "Companies",
                'action' => 'new'
            ]);

            return;
        }

        $this->dispatcher->forward([
            'controller' => "Companies",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a companie edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "companies",
                'action' => 'index'
            ]);

            return;
        }

        $companyId = $this->request->getPost("companyId");
        $companie = Companies::findFirstBycompanyId($companyId);

        if (!$companie) {
            $this->flash->error("companie does not exist " . $companyId);

            $this->dispatcher->forward([
                'controller' => "companies",
                'action' => 'index'
            ]);

            return;
        }

        $companie->setName($this->request->getPost("name"));
        $companie->setFullname($this->request->getPost("fullName"));
        $companie->setTin($this->request->getPost("TIN"));
        $companie->setRegion($this->request->getPost("region"));
        $companie->setUserid($this->request->getPost("userId"));


        if (!$companie->save()) {

            foreach ($companie->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "companies",
                'action' => 'edit',
                'params' => [$companie->getCompanyid()]
            ]);

            return;
        }

        $this->flash->success("companie was updated successfully");

        $this->dispatcher->forward([
            'controller' => "companies",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a companie
     *
     * @param string $companyId
     */
    public function deleteAction($companyId)
    {
        $companie = Companies::findFirstBycompanyId($companyId);
        if (!$companie) {
            $this->flash->error("companie was not found");

            $this->dispatcher->forward([
                'controller' => "companies",
                'action' => 'index'
            ]);

            return;
        }

        if (!$companie->delete()) {

            foreach ($companie->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "companies",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("companie was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "companies",
            'action' => "index"
        ]);
    }

}
