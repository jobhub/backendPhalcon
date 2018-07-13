<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class FavouriteUsersAPIController extends Controller
{
    public function setFavouriteAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userIdSubject = $auth['id'];
            $userIdObject = $this->request->getPost('userId');

            $fav = Favoriteusers::findFirst(["userObject = :userIdObject: AND userSubject = :userIdSubject:",
                "bind" => [
                "userIdObject" => $userIdObject,
                "userIdSubject" => $userIdSubject,
            ]
            ]);

            if(!$fav){

                $fav = new Favoriteusers();
                $fav->setUserObject($userIdObject);
                $fav->setUserSubject($userIdSubject);

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
            $userSubject = $auth['id'];
            $userObject = $this->request->getPost('userObject');

            $fav = FavoriteUsers::findFirst(["userObject = :userObject: AND userSubject = :userSubject:",
                "bind" => [
                    "userObject" => $userObject,
                    "userSubject" => $userSubject,
                ]
            ]);

            if($fav){
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
                    "errors" => ["Пользователь не подписан"]
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

            /*$fav = Favoritecategories::find(["users_userId = :userId:", "bind" =>
            ["userId" => $userId]]);

            return json_encode($fav);*/

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
