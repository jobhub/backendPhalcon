<?php

namespace App\Controllers;

use App\Models\PhonesPoints;
use App\Services\PointService;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;


use App\Models\Companies;
use App\Models\TradePoints;
use App\Models\Accounts;
use App\Services\CompanyService;
use App\Services\AccountService;
use App\Services\ImageService;
use App\Services\PhoneService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class PhonesAPIController
 * Контроллер для работы с номерами телефонов.
 * Содержит методы для добавления, изменения и удаления номеров телефонов
 * для пользователей, компаний и точек оказания услуг.
 */
class PhonesAPIController extends AbstractController
{
    /**
     * Добавляет телефон для указанной компании
     * @method POST
     * @params integer company_id, string phone или integer phone_id
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function addPhoneToCompanyAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['company_id'] = $inputData->company_id;
        $data['phone'] = $inputData->phone;
        $data['phone_id'] = $inputData->phone_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $errors = null;

            if (empty(trim($data['company_id']))) {
                $errors['company_id'] = 'Missing required parameter "company_id"';
            }

            if (empty(trim($data['phone'])) && empty(trim($data['phone_id']))) {
                $errors['phone'] = 'Missing required parameter "phone" or "phone_id"';
            }

            if ($errors != null) {
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            if (!Accounts::checkUserHavePermissionToCompany($userId, $data['company_id'], 'addPhoneToCompany')) {
                throw new Http403Exception('Permission error');
            }

            if (empty(trim($data['phone']))) {
                $phone = $this->phoneService->getPhoneById($data['phone_id']);
                $data['phone'] = $phone->getPhone();
            }

            $phoneCompany = $this->phoneService->addPhoneToCompany($data['phone'], $data['company_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_UNABLE_ADD_PHONE_TO_COMPANY:
                case PhoneService::ERROR_UNABLE_CREATE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_PHONE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Phone was successfully added to company', ['phone_id' => $phoneCompany->getPhoneId()]);
    }

    /**
     * Добавляет телефон для указанной точки оказания услуг
     * @method POST
     * @params integer point_id, string phone или integer phone_id
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function addPhoneToTradePointAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['point_id'] = $inputData->point_id;
        $data['phone'] = $inputData->phone;
        $data['phone_id'] = $inputData->phone_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $errors = null;

            if (empty(trim($data['point_id']))) {
                $errors['point_id'] = 'Missing required parameter "point_id"';
            }

            if (empty(trim($data['phone'])) && empty(trim($data['phone_id']))) {
                $errors['phone'] = 'Missing required parameter "phone" or "phone_id"';
            }

            if ($errors != null) {
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            $point = $this->pointService->getPointById($data['point_id']);

            if (!Accounts::checkUserHavePermission($userId, $point->getAccountId(), 'addPhoneToPoint')) {
                throw new Http403Exception('Permission error');
            }

            if (empty(trim($data['phone']))) {
                $phone = $this->phoneService->getPhoneById($data['phone_id']);
                $data['phone'] = $phone->getPhone();
            }

            $phoneCompany = $this->phoneService->addPhoneToPoint($data['phone'], $data['point_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_UNABLE_ADD_PHONE_TO_POINT:
                case PhoneService::ERROR_UNABLE_CREATE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_PHONE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Phone was successfully added to company', ['phone_id' => $phoneCompany->getPhoneId()]);
    }

    /**
     * Убирает телефон из списка телефонов компании
     *
     * @method DELETE
     *
     * @param int $phone_id
     * @param int $company_id
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deletePhoneFromCompanyAction($phone_id, $company_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            if (!Accounts::checkUserHavePermissionToCompany($userId, $company_id, 'deletePhoneFromCompany')) {
                throw new Http403Exception('Permission error');
            }

            $this->db->begin();
            $phoneCompany = $this->phoneService->getPhoneCompanyById($phone_id,$company_id);
            $this->phoneService->deletePhoneCompany($phoneCompany);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case PhoneService::ERROR_UNABLE_DELETE_PHONE_FROM_COMPANY:
                case PhoneService::ERROR_UNABLE_DELETE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case PhoneService::ERROR_PHONE_NOT_FOUND:
                case PhoneService::ERROR_PHONE_COMPANY_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();

        return self::successResponse('Phone was successfully deleted from company');
    }

    /**
     * Убирает телефон из списка телефонов точки
     *
     * @method DELETE
     *
     * @param int $phone_id
     * @param int $point_id
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deletePhoneFromTradePointAction($phone_id, $point_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $point = $this->pointService->getPointById($point_id);

            if (!Accounts::checkUserHavePermission($userId, $point->getAccountId(), 'addPhoneToPoint')) {
                throw new Http403Exception('Permission error');
            }

            $this->db->begin();

            $phonePoint = $this->phoneService->getPhonePointById($phone_id,$point_id);
            $this->phoneService->deletePhonePoint($phonePoint);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case PhoneService::ERROR_UNABLE_DELETE_PHONE_FROM_POINT:
                case PhoneService::ERROR_UNABLE_DELETE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case PhoneService::ERROR_PHONE_NOT_FOUND:
                case PhoneService::ERROR_PHONE_POINT_NOT_FOUND:
                case PointService::ERROR_POINT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();
        return self::successResponse('Phone was successfully deleted from point');
    }


    /**
     * Изменяет определенный номер телефона у определенной точки услуг
     * @method PUT
     * @params integer point_id, string phone (новый) и integer phone_id (старый)
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function editPhoneInTradePointAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['point_id'] = $inputData->point_id;
        $data['phone'] = $inputData->phone;
        $data['phone_id'] = $inputData->phone_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $errors = null;

            if (empty(trim($data['point_id']))) {
                $errors['point_id'] = 'Missing required parameter "point_id"';
            }

            if (empty(trim($data['phone']))) {
                $errors['phone'] = 'Missing required parameter "phone"';
            }

            if (empty(trim($data['phone_id']))) {
                $errors['phone'] = 'Missing required parameter "phone_id"';
            }

            if ($errors != null) {
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            $point = $this->pointService->getPointById($data['point_id']);

            if (!Accounts::checkUserHavePermission($userId, $point->getAccountId(), 'changePhonesInPoint')) {
                throw new Http403Exception('Permission error');
            }

            $phonePoint = $this->phoneService->getPhonePointById($data['phone_id'],$data['point_id']);
            $this->phoneService->deletePhonePoint($phonePoint);

            $phoneCompany = $this->phoneService->addPhoneToPoint($data['phone'], $data['point_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_UNABLE_ADD_PHONE_TO_POINT:
                case PhoneService::ERROR_UNABLE_DELETE_PHONE_FROM_POINT:
                case PhoneService::ERROR_UNABLE_DELETE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_PHONE_NOT_FOUND:
                case PhoneService::ERROR_PHONE_POINT_NOT_FOUND:
                case PointService::ERROR_POINT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Phone was successfully changed', ['phone_id' => $phoneCompany->getPhoneId()]);
    }

    /*public function testAction(){
        $response = new Response();
        $phonesCompany = PhonesCompanies::findFirst(["companyId = :companyId: and phoneId = :phoneId:", "bind" =>
            ["companyId" => $this->request->getPut("companyId"),
                "phoneId" => $this->request->getPut("phoneId")]
        ]);

        //$phonesCompany->setPhoneId($this->request->getPut("phoneId2"));
        $phonesCompany->setCompanyId(21);

        if (!$phonesCompany->save()) {

            $this->db->rollback();
            $errors = [];
            foreach ($phonesCompany->getMessages() as $message) {
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
                'status' => STATUS_OK
            ]
        );
        return $response;
    }*/

    /**
     * Изменяет определенный номер телефона у определенной компании
     * @method PUT
     * @params integer company_id, string phone (новый) и integer phone_id (старый)
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function editPhoneInCompanyAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['company_id'] = $inputData->company_id;
        $data['phone'] = $inputData->phone;
        $data['phone_id'] = $inputData->phone_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $errors = null;

            if (empty(trim($data['company_id']))) {
                $errors['company_id'] = 'Missing required parameter "company_id"';
            }

            if (empty(trim($data['phone']))) {
                $errors['phone'] = 'Missing required parameter "phone"';
            }

            if (empty(trim($data['phone_id']))) {
                $errors['phone'] = 'Missing required parameter "phone_id"';
            }

            if ($errors != null) {
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            if (!Accounts::checkUserHavePermissionToCompany($userId, $data['company_id'], 'changePhonesInPoint')) {
                throw new Http403Exception('Permission error');
            }

            $phoneCompany = $this->phoneService->getPhoneCompanyById($data['phone_id'],$data['company_id']);
            $this->phoneService->deletePhoneCompany($phoneCompany);

            $phoneCompany = $this->phoneService->addPhoneToCompany($data['phone'], $data['company_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_UNABLE_ADD_PHONE_TO_POINT:
                case PhoneService::ERROR_UNABLE_DELETE_PHONE_FROM_COMPANY:
                case PhoneService::ERROR_UNABLE_DELETE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_PHONE_NOT_FOUND:
                case PhoneService::ERROR_PHONE_COMPANY_NOT_FOUND:
                case PointService::ERROR_POINT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Phone was successfully changed', ['phone_id' => $phoneCompany->getPhoneId()]);
    }

    /**
     * Добавляет телефон пользователю.
     * Приватный метод.
     *
     * @method POST
     *
     * @params string phone или integer phone_id
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function addPhoneToUserAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['phone'] = $inputData->phone;
        $data['phone_id'] = $inputData->phone_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $errors = null;
            if (empty(trim($data['phone'])) && empty(trim($data['phone_id']))) {
                $errors['phone'] = 'Missing required parameter "phone" or "phone_id"';
            }

            if ($errors != null) {
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            if (empty(trim($data['phone']))) {
                $phone = $this->phoneService->getPhoneById($data['phone_id']);
                $data['phone'] = $phone->getPhone();
            }

            $phoneUser = $this->phoneService->addPhoneToUser($data['phone'], $userId);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_UNABLE_ADD_PHONE_TO_USER:
                case PhoneService::ERROR_UNABLE_CREATE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case PhoneService::ERROR_PHONE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Phone was successfully added to user', ['phone_id' => $phoneUser->getPhoneId()]);

    }

    /**
     * Убирает телефон из списка телефонов пользователя.
     * Приватный метод.
     *
     * @method DELETE
     *
     * @param int $phone_id
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deletePhoneFromUserAction($phone_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $this->db->begin();

            $phoneUser = $this->phoneService->getPhoneUserById($phone_id,$userId);
            $this->phoneService->deletePhoneUser($phoneUser);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case PhoneService::ERROR_UNABLE_DELETE_PHONE_FROM_USER:
                case PhoneService::ERROR_UNABLE_DELETE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case PhoneService::ERROR_PHONE_NOT_FOUND:
                case PhoneService::ERROR_PHONE_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();
        return self::successResponse('Phone was successfully deleted from user');
    }
}
