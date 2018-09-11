<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class UserLocationAPIController extends Controller
{
    /**
     * Устанавливает текущее местоположение текущего пользователя.
     * Приватный метод.
     *
     * @method POST
     * @params latitude;
     * @params longitude;
     * @return string - json array результат операции.
     */
    public function setLocationAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get("auth");
            $response = new Response();
            $userId = $auth['id'];
            $userlocation = UserLocation::findFirstByUserid($userId);
            if (!$userlocation) {

                $userlocation = new UserLocation();
                $userlocation->setLatitude($this->request->getPost('latitude'));
                $userlocation->setLongitude($this->request->getPost('longitude'));
                $userlocation->setUserId($userId);
                $userlocation->setLastTime(time());

                if (!$userlocation->save()) {
                    $errors = [];
                    foreach ($userlocation->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }

            } else{
                $userlocation->setLatitude($this->request->getPost('latitude'));
                $userlocation->setLongitude($this->request->getPost('longitude'));
                $userlocation->setLastTime(time());

                if (!$userlocation->update()) {
                    $errors = [];
                    foreach ($userlocation->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }
            }

            $response->setJsonContent([
                "status" => STATUS_OK
            ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Ищет пользователей по поисковой строке и внутри заданных координат.
     * @access public
     *
     * @method POST
     *
     * @params string query
     * @params center - [longitude => ..., latitude => ...] - центральная точка
     * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
     * @return string - json array - массив пользователей.
     *          [status, users=>[userid, email, phone, firstname, lastname, patronymic,
     *          longitude, latitude, lasttime,male, birthday,pathtophoto]]
     */
    public function findUsersAction(){
        if ($this->request->isPost()) {
            $auth = $this->session->get("auth");
            $response = new Response();
            $userId = $auth['id'];

            $center = $this->request->getPost('center');
            $diagonal = $this->request->getPost('diagonal');

            $longitudeHR = $diagonal['longitude'];
            $latitudeHR = $diagonal['latitude'];

            $diffLong = $diagonal['longitude'] - $center['longitude'];
            $longitudeLB = $center['longitude'] - $diffLong;

            $diffLat = $diagonal['latitude'] - $center['latitude'];
            $latitudeLB = $center['latitude'] - $diffLat;

            $results = UserLocation::findUsersByQuery($this->request->getPost('query'),
                $longitudeHR,$latitudeHR,$longitudeLB,$latitudeLB);

            $response->setJsonContent([
                "status" => STATUS_OK,
                'users' => $results
            ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает данные для автокомплита поиска по пользователям.
     *
     * @access public
     *
     * @method POST
     *
     * @params string query
     * @params center - [longitude => ..., latitude => ...] - центральная точка
     * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
     * @return string - json array - массив пользователей.
     *          [status, users=>[userid, firstname, lastname, patronymic]]
     */
    public function getAutoCompleteForSearchAction(){
        if ($this->request->isPost()) {
            $auth = $this->session->get("auth");
            $response = new Response();
            $userId = $auth['id'];

            $center = $this->request->getPost('center');
            $diagonal = $this->request->getPost('diagonal');

            $longitudeHR = $diagonal['longitude'];
            $latitudeHR = $diagonal['latitude'];

            $diffLong = $diagonal['longitude'] - $center['longitude'];
            $longitudeLB = $center['longitude'] - $diffLong;

            $diffLat = $diagonal['latitude'] - $center['latitude'];
            $latitudeLB = $center['latitude'] - $diffLat;

            $results = UserLocation::getAutoComplete($this->request->getPost('query'),
                $longitudeHR,$latitudeHR,$longitudeLB,$latitudeLB);

            $response->setJsonContent([
                "status" => STATUS_OK,
                'users' => $results
            ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает данные аналогичные поиску, но без поиска по id пользователя.
     *
     * @access public
     *
     * @method POST
     *
     * @params int userId
     * @return string - json array - массив пользователей.
     *          [status, users=>[userid, email, phone, firstname, lastname, patronymic,
     *          longitude, latitude, lasttime,male, birthday,pathtophoto]]
     */
    public function getUserByIdAction(){
        if ($this->request->isPost()) {
            $auth = $this->session->get("auth");
            $response = new Response();
            $userId = $auth['id'];

            $results = UserLocation::getUserinfo($this->request->getPost('userId'));

            $response->setJsonContent([
                "status" => STATUS_OK,
                'users' => $results
            ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}