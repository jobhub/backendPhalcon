<?php

namespace App\Controllers;

use App\Models\Accounts;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Models\Companies;
use App\Models\TradePoints;
use App\Models\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

use App\Services\PointService;

/**
 * Class TradePointsAPIController
 * Контроллер для работы с точками оказания услуг.
 * Реализует CRUD для точек оказания услуг.
 */
class TradePointsAPIController extends AbstractController
{
    /**
     * Возвращает точки предоставления услуг для пользователя или для указанной компании пользователя.
     * @access private
     * @method GET
     * @param integer $company_id
     * @return string - json array of [status, [TradePoint, phones]], если все успешно,
     * или json array в формате Status в ином случае
     */
    public function getPointsAction($company_id = null)
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        if ($company_id != null) {

            if (!Accounts::checkUserHavePermissionToCompany($userId, $company_id, 'getPoints')) {
                throw new Http403Exception('Permission error');
            }

            return TradePoints::findPointsByCompany($company_id);
        } else {
            return TradePoints::findPointsByUser($userId);
        }
    }

    /**
     * Возвращает точки предоставления услуг назначенные текущему пользователю
     * @access private
     * @method GET
     * @param  int $manager_user_id
     * @return string - json array of [TradePoint, phones]
     */
    public function getPointsForUserManagerAction($manager_user_id = null)
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        if ($manager_user_id == null) {
            return TradePoints::handlePointsFromArray(TradePoints::find(['columns' => TradePoints::publicColumnsInStr,
                'conditions' => 'user_manager = :userId:', 'bind' => ['userId' => $userId]])->toArray());
        } else {
            return TradePoints::handlePointsFromArray(TradePoints::find(['columns' => TradePoints::publicColumnsInStr,
                'conditions' => 'user_manager = :userId:', 'bind' => ['userId' => $manager_user_id]])->toArray());
        }
    }


    /**
     * Добавляет точку оказания услуг к компании
     * @access private
     * @method POST
     *
     * @params (Обязательные)   string name, double latitude, double longitude, int account_id
     * @params (Необязательные) string email, string website, string address, string fax,
     * @params (Необязательные) (int manager_user_id, int company_id) - парой
     * @return array с point_id
     */
    public function addTradePointAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['name'] = $inputData->name;
        $data['latitude'] = $inputData->latitude;
        $data['longitude'] = $inputData->longitude;
        $data['address'] = $inputData->address;
        $data['website'] = $inputData->website;
        $data['email'] = $inputData->email;
        $data['fax'] = $inputData->fax;
        $data['manager_user_id'] = $inputData->manager_user_id;
        $data['company_id'] = $inputData->company_id;
        $data['account_id'] = $inputData->account_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if (empty(trim($data['account_id'])))
                $data['account_id'] = Accounts::findForUserDefaultAccount($userId)->getId();

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'createTradePoint'))
                throw new Http403Exception('Permission error');

            $point = $this->pointService->createPoint($data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case PointService::ERROR_UNABLE_CREATE_POINT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Trade point was successfully created', ['point_id' => $point->getPointId()]);
    }

    /**
     * Вспомогательная функция для добавления точки оказания услуг. Нужна для предоставления
     * функции добавления точек оказания услуг из других контроллеров.
     * Принимает ассоциативный массив params со следующими параметрами:
     * (Обязательные)   string name, double latitude, double longitude,
     * (Необязательные) string email, string webSite, string address, string fax,
     * (Необязательные) (int userManagerId, int companyId) - парой.
     * Если userId равен null, то берет id и сессии
     * Недоступна при непосредственных запросах.
     * @param $params
     *
     * @return string - json array в формате Status. Если успешно, то еще и id созданной точки.
     */
    /*public function addTradePoint($params)
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $response = new Response();

        $point = new TradePoints();

        if ($params["companyId"]) {
            $company = Companies::findFirstByCompanyid($params["companyId"]);

            if (!Companies::checkUserHavePermission($userId, $company->getCompanyId(), 'addPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $point->setSubjectId($params["companyId"]);
            $point->setSubjectType(1);
            if ($params["userManagerId"])
                $point->setUserManager($params["userManagerId"]);
            else {
                $point->setUserManager($userId);
            }
        } else {
            $point->setSubjectId($userId);
            $point->setSubjectType(0);
            $point->setUserManager($userId);
        }

        $point->setName($params["name"]);
        $point->setEmail($params["email"]);
        $point->setWebSite($params["webSite"]);
        $point->setLatitude($params["latitude"]);
        $point->setLongitude($params["longitude"]);
        $point->setAddress($params["address"]);
        $point->setFax($params["fax"]);
        $point->setTime($params["time"]);
        $point->setPositionVariable($params["positionvariable"]);
        $point->setUserManager($params["userId"]);

        if (!$point->save()) {
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
                "pointId" => $point->getPointId(),
                "status" => STATUS_OK
            ]
        );

        return $response;
    }*/

    /**
     * Редактирует указанную точку оказания услуг
     *
     * @method PUT
     *
     * @param (Обязательные)   int point_id string name, double latitude, double longitude,
     *        (Необязательные) string email, string website, string address, string fax, string time, int manager_user_id.
     *
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function editTradePointAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['point_id'] = $inputData->point_id;
        $data['name'] = $inputData->name;
        $data['latitude'] = $inputData->latitude;
        $data['longitude'] = $inputData->longitude;
        $data['region_id'] = $inputData->region_id;
        $data['website'] = $inputData->website;
        $data['email'] = $inputData->email;
        $data['fax'] = $inputData->fax;
        $data['time'] = $inputData->time;
        $data['address'] = $inputData->address;
        $data['manager_user_id'] = $inputData->manager_user_id;

        try {

            //validation
            if (empty(trim($data['point_id']))) {
                $errors['point_id'] = 'Missing required parameter "point_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $point = $this->pointService->getPointById($data['point_id']);

            if (!Accounts::checkUserHavePermission($userId, $point->getAccountId(), 'editTradePoint')) {
                throw new Http403Exception('Permission error');
            }

            $this->pointService->changePoint($point, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case PointService::ERROR_UNABLE_CHANGE_POINT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case PointService::ERROR_POINT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Trade point was successfully changed');
    }


    /**
     * Удаляет указанную точку оказания услуг
     *
     * @method DELETE
     *
     * @param (Обязательные) $point_id
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deleteTradePointAction($point_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $point = $this->pointService->getPointById($point_id);

            if (!Accounts::checkUserHavePermission($userId, $point->getAccountId(), 'deletePoint')) {
                throw new Http403Exception('Permission error');
            }

            $this->pointService->deletePoint($point);


        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case PointService::ERROR_UNABLE_DELETE_POINT:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case PointService::ERROR_POINT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Trade point was successfully deleted');
    }

    /**
     * Возвращает публичную информацию об указанной точке оказания услуг.
     * Публичный доступ.
     *
     * @access public
     * @method GET
     *
     * @param $point_id
     * @return array - {point,[services]}
     */
    public function getPointInfoAction($point_id)
    {
        $point = TradePoints::findPointById($point_id);

        if (!$point) {
            throw new Http400Exception('Trade point don\'t exists', PointService::ERROR_POINT_NOT_FOUND);
        }

        $point = TradePoints::handlePointsFromArray([$point->toArray()]);
        return ['point' => $point, 'services' => Services::findServicesForPoint($point_id)];
    }
}
