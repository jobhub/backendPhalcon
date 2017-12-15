<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class CategoriesController extends ControllerBase
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
    }

    /**
     * Searches for categories
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Categories', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "categoryId";

        $categories = Categories::find($parameters);
        if (count($categories) == 0) {
            $this->flash->notice("The search did not find any categories");

            $this->dispatcher->forward([
                "controller" => "categories",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $categories,
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
     * Edits a categorie
     *
     * @param string $categoryId
     */
    public function editAction($categoryId)
    {
        if (!$this->request->isPost()) {

            $categorie = Categories::findFirstBycategoryId($categoryId);
            if (!$categorie) {
                $this->flash->error("categorie was not found");

                $this->dispatcher->forward([
                    'controller' => "categories",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->categoryId = $categorie->getCategoryid();

            $this->tag->setDefault("categoryId", $categorie->getCategoryid());
            $this->tag->setDefault("categoryName", $categorie->getCategoryname());
            
        }
    }

    /**
     * Creates a new categorie
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "categories",
                'action' => 'index'
            ]);

            return;
        }

        $categorie = new Categories();
        $categorie->setCategoryname($this->request->getPost("categoryName"));
        

        if (!$categorie->save()) {
            foreach ($categorie->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "categories",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("categorie was created successfully");

        $this->dispatcher->forward([
            'controller' => "categories",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a categorie edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "categories",
                'action' => 'index'
            ]);

            return;
        }

        $categoryId = $this->request->getPost("categoryId");
        $categorie = Categories::findFirstBycategoryId($categoryId);

        if (!$categorie) {
            $this->flash->error("categorie does not exist " . $categoryId);

            $this->dispatcher->forward([
                'controller' => "categories",
                'action' => 'index'
            ]);

            return;
        }

        $categorie->setCategoryname($this->request->getPost("categoryName"));
        

        if (!$categorie->save()) {

            foreach ($categorie->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "categories",
                'action' => 'edit',
                'params' => [$categorie->getCategoryid()]
            ]);

            return;
        }

        $this->flash->success("categorie was updated successfully");

        $this->dispatcher->forward([
            'controller' => "categories",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a categorie
     *
     * @param string $categoryId
     */
    public function deleteAction($categoryId)
    {
        $categorie = Categories::findFirstBycategoryId($categoryId);
        if (!$categorie) {
            $this->flash->error("categorie was not found");

            $this->dispatcher->forward([
                'controller' => "categories",
                'action' => 'index'
            ]);

            return;
        }

        if (!$categorie->delete()) {

            foreach ($categorie->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "categories",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("categorie was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "categories",
            'action' => "index"
        ]);
    }

}
