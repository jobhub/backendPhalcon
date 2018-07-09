<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class TradePointsAPIController extends Controller
{
    /**
     * Возвращает точки предоставления услуг для компании
     *
     * @method GET
     * @param integer $companyId
     * @return string - json array of TradePoints, если все успешно,
     * или json array в формате Status в ином случае
     */
    public function getPointsForCompanyAction($companyId)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {

            $response = new Response();

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $company = Companies::findFirstByCompanyId($companyId);

            if (!$company || ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $tradePoints = TradePoints::findByCompanyId($companyId);

            return json_encode($tradePoints);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает точки предоставления услуг назначенные текущему пользователю
     *
     * @method GET
     * @param  int $userIdManager необязательный
     * @return string - json array of TradePoints
     */
    public function getPointsForUserManagerAction($userIdManager = null)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if ($userIdManager == null || $userIdManager == $userId) {
                $tradePoints = TradePoints::findByUserManager($userId);
            } else {
                $trades = TradePoints::findByUserManager($userIdManager);
                $tradePoints = [];
                foreach ($trades as $point) {
                    if ($point->companies->getUserId() == $userId) {
                        $tradePoints[] = $point;
                    }
                }
            }
            return json_encode($tradePoints);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет точку оказания услуг к компании
     *
     * @method POST
     *
     * @param (Обязательные)   int companyId, string name, double latitude, double longitude,
     *        (Необязательные) string email, string webSite, string address, string fax, int userId
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function addTradePointAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirstByCompanyId($this->request->getPost("companyId"));

            if (!$company || ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $point = new TradePoints();

            $point->setCompanyId($this->request->getPost("companyId"));
            $point->setName($this->request->getPost("name"));
            $point->setEmail($this->request->getPost("email"));
            $point->setWebSite($this->request->getPost("webSite"));
            $point->setLatitude($this->request->getPost("latitude"));
            $point->setLongitude($this->request->getPost("longitude"));
            $point->setAddress($this->request->getPost("address"));
            $point->setFax($this->request->getPost("fax"));
            $point->setTime($this->request->getPost("time"));
            $point->setUserManager($this->request->getPost("userId"));

            if (!$point->save()) {

                foreach ($point->getMessages() as $message) {
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

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Редактирует указанную точку оказания услуг
     *
     * @method PUT
     *
     * @param (Обязательные)   int pointId, int companyId, string name, double latitude, double longitude,
     *        (Необязательные) string email, string webSite, string address, string fax, int userId
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function editTradePointAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirstByCompanyId($this->request->getPut("companyId"));

            $point = TradePoints::findFirstByPointId($this->request->getPut("pointId"));

            if (!$company || !$point ||
                ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR && $point->getUserManager() != $userId)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $point->setCompanyId($this->request->getPut("companyId"));
            $point->setName($this->request->getPut("name"));
            $point->setEmail($this->request->getPut("email"));
            $point->setWebSite($this->request->getPut("webSite"));
            $point->setLatitude($this->request->getPut("latitude"));
            $point->setLongitude($this->request->getPut("longitude"));
            $point->setAddress($this->request->getPut("address"));
            $point->setFax($this->request->getPut("fax"));
            $point->setTime($this->request->getPut("time"));
            $point->setUserManager($this->request->getPut("userId"));

            if (!$point->save()) {

                foreach ($point->getMessages() as $message) {
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

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }


    /**
     * Удаляет указанную точку оказания услуг
     *
     * @method DELETE
     *
     * @param (Обязательные) $pointId
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deleteTradePointAction($pointId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $point = TradePoints::findFirstByPointId($pointId);

            if (!$point) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая точка оказания услуг не существует']
                    ]
                );
                return $response;
            }

            $company = $point->companies;

            if (!$company ||
                ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$point->delete()) {
                $errors = [];
                foreach ($point->getMessages() as $message) {
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

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
