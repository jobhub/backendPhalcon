<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

class FavouriteCompaniesAPIController extends Controller
{
    /**
     * Подписывает текущего пользователя на компанию
     *
     * @method POST
     *
     * @param companyId
     *
     * @return Response с json ответом в формате Status
     */
    public function setFavouriteAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $companyId = $this->request->getPost('companyId');

            $fav = FavoriteCompanies::findFirst(["userId = :userId: AND companyId = :companyId:",
                "bind" => [
                "userId" => $userId,
                "companyId" => $companyId,
            ]
            ]);

            if(!$fav){

                $fav = new FavoriteCompanies();
                $fav->setUserId($userId);
                $fav->setCompanyId($companyId);

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
                    "errors" => ["Пользователь уже подписан на компанию"]
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }


    /**
     * Отменяет подписку на компанию
     *
     * @method DELETE
     *
     * @param $companyId
     *
     * @return Response с json ответом в формате Status
     */
    public function deleteFavouriteAction($companyId)
    {
        if ($this->request->isDelete()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $fav = FavoriteCompanies::findFirst(["userId = :userId: AND companyId = :companyId:",
                "bind" => [
                    "userId" => $userId,
                    "companyId" => $companyId,
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
                    "errors" => ["Пользователь не подписан на компанию"]
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function getFavouritesAction()
    {

        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $favs = FavoriteCompanies::find(["userId = :userId: ",
                "bind" => [
                    "userId" => $userId,
                ]
            ]);

            return json_encode($favs);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
