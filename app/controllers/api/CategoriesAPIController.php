<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

class CategoriesAPIController extends Controller
{
    /**
     * Index action
     */
    public function indexAction()
    {
        if ($this->request->isPost() || $this->request->isGet()) {
            $categories = Categories::find();
            return json_encode($categories);
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function setFavouriteAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $categoryId = $this->request->getPost('categoryId');

            $fav = Favoritecategories::findFirst(["userId = :userId: AND categoryId = :categoryId:",
                "bind" => [
                    "userId" => $userId,
                    "categoryId" => $categoryId,
                ]
            ]);

            if (!$fav) {

                $fav = new Favoritecategories();
                $fav->setCategoryId($categoryId);
                $fav->setUserId($userId);

                if (!$fav->save()) {
                    foreach ($fav->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => "WRONG_DATA",
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }

                $response->setJsonContent(
                    [
                        "status" => "OK",
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "ALREADY_EXISTS",
                    "errors" => ["already exists"]
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function deleteFavouriteAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $categoryId = $this->request->getPost('categoryId');

            $fav = Favoritecategories::findFirst(["userId = :userId: AND categoryId = :categoryId:",
                "bind" => [
                    "userId" => $userId,
                    "categoryId" => $categoryId,
                ]
            ]);

            if ($fav) {
                if (!$fav->delete()) {
                    foreach ($fav->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => "WRONG_DATA",
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }

                $response->setJsonContent(
                    [
                        "status" => "OK",
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "WRONG_DATA",
                    "errors" => ["don't exists"]
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function getFavouriteAction()
    {

        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $fav = Favoritecategories::find(["userId = :userId:", "bind" =>
                ["userId" => $userId]]);

            return json_encode($fav);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает категории
     *
     * @method GET
     *
     * @return string - json array с категориями
     */
    public function getCategoriesAction()
    {
        if ($this->request->isGet()) {

            $categories = Categories::find(['order' => 'parentId DESC']);

            $categories2 = [];
            foreach ($categories as $category) {
                /*if ($category->getParentId() == null) {
                    $categories2[$category->getCategoryId()] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                        'description' => $category->getDescription(), 'img' => $category->getImg(),
                        'child' => []];
                } else{
                    $categories2[$category->getParentId()]['child'][] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                        'description' => $category->getDescription(), 'img' => $category->getImg(),
                        'child' => []];
                }*/
                if ($category->getParentId() == null) {
                    $categories2[] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                        'description' => $category->getDescription(), 'img' => $category->getImg(),
                        'child' => []];
                } else{
                    for($i = 0; $i < count($categories2); $i++)
                        if($categories2[$i]['id'] == $category->getParentId()) {
                            $categories2[$i]['child'][] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                                'description' => $category->getDescription(), 'img' => $category->getImg(),
                                'child' => [], 'check' => false];
                            break;
                        }
                }
            }
            $response = new Response();
            $response->setJsonContent($categories2);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function editCategoryAction()
    {
        if ($this->request->isPost()) {

            $category = Categories::findFirstByCategoryId($this->request->getPost('categoryId'));

            $category->setDescription($this->request->getPost('description'));
            $category->setImg($this->request->getPost('img'));
            $category->setParentId($this->request->getPost('parentId'));
            $category->setCategoryName($this->request->getPost('categoryName'));

            $category->save();
            return $category->save();

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function addCategoryAction()
    {
        if ($this->request->isPost()) {

            $category = new Categories();

            $category->setDescription($this->request->getPost('description'));
            $category->setImg($this->request->getPost('img'));
            $category->setParentId($this->request->getPost('parentId'));
            $category->setCategoryName($this->request->getPost('categoryName'));

            $category->save();
            return $category->save();

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
