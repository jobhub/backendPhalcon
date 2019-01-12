<?php

namespace App\Controllers;

use App\Models\CompanyRole;
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

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Контроллер для работы с компаниями.
 * Реализует CRUD для компаний, содержит методы для настройки менеджеров.
 */
class CompaniesAPIController extends AbstractController
{
    /**
     * Возвращает компании текущего пользователя
     *
     * @param $with_points
     *
     * @method GET
     * @return array - json array компаний
     */
    public function getCompaniesAction($with_points = false)
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        $result['companies'] = Companies::findCompaniesByUserOwner($userId);

        if ($with_points && $with_points != 'false') {
            $result2 = [];
            foreach ($result['companies'] as $company) {
                $points = TradePoints::findPointsByCompany($company['company_id']);
                $result2[] = ['company' => $company, 'points' => $points];
            }

            return $result2;
        }

        return $result;
    }

    /**
     * Создает компанию.
     *
     * @method POST
     * @params (Обязательные)name, full_name
     * @params (необязательные) tin, region_id, website, email, description
     * @return int company_id
     */
    public function addCompanyAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['name'] = $inputData->name;
        $data['full_name'] = $inputData->full_name;
        $data['tin'] = $inputData->tin;
        $data['region_id'] = $inputData->region_id;
        $data['website'] = $inputData->website;
        $data['email'] = $inputData->email;
        $data['description'] = $inputData->description;

        $this->db->begin();
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $company = $this->companyService->createCompany($data, $userId);

            $this->accountService->createAccount([
                'user_id' => $userId,
                'company_id' => $company->getCompanyId(),
                'company_role_id' => CompanyRole::ROLE_OWNER_ID
            ]);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case CompanyService::ERROR_UNABLE_CREATE_COMPANY:
                case AccountService::ERROR_UNABLE_CREATE_ACCOUNT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();

        return self::successResponse('Company was successfully created', ['company_id' => $company->getCompanyId()]);
    }

    /**
     * Удаляет указанную компанию
     * @method DELETE
     *
     * @param $company_id
     * @return string - json array Status
     */
    public function deleteCompanyAction($company_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $company = $this->companyService->getCompanyById($company_id);

            if (!Accounts::checkUserHavePermissionToCompany($userId, $company->getCompanyId(), 'deleteCompany')) {
                throw new Http403Exception('Permission error');
            }

            $this->companyService->deleteCompany($company);


        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_UNABLE_DELETE_COMPANY:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Company was successfully deleted');
    }

    /**
     * Редактирует данные компании
     * @method PUT
     * @params company_id, name, full_name, tin, region_id, website, email, description
     */
    public function editCompanyAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['company_id'] = $inputData->company_id;
        $data['name'] = $inputData->name;
        $data['full_name'] = $inputData->full_name;
        $data['tin'] = $inputData->tin;
        $data['region_id'] = $inputData->region_id;
        $data['website'] = $inputData->website;
        $data['email'] = $inputData->email;
        $data['description'] = $inputData->description;

        try {

            //validation
            if (empty(trim($data['company_id']))) {
                $errors['company_id'] = 'Missing required parameter "company_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $company = $this->companyService->getCompanyById($data['company_id']);

            if (!Accounts::checkUserHavePermissionToCompany($userId, $company->getCompanyId(), 'editCompany')) {
                throw new Http403Exception('Permission error');
            }

            unset($data['company_id']);

            $this->companyService->changeCompany($company, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_UNABLE_CHANGE_COMPANY:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Company was successfully changed');
    }

    /**
     * Устанавливает логотип для компании. Сам логотип должен быть передан в файлах. ($_FILES)
     * @method POST
     * @params company_id
     * @return Response
     */
    public function setCompanyLogotypeAction()
    {
        try {
            /*$sender = $this->request->getJsonRawBody();

            $data['news_id'] = $sender->news_id;*/

            $data['company_id'] = $this->request->getPost('company_id');

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //validation
            if (empty(trim($data['company_id']))) {
                $errors['company_id'] = 'Missing required parameter "company_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $company = $this->companyService->getCompanyById($data['company_id']);

            if (!Accounts::checkUserHavePermissionToCompany($userId, $company->getCompanyId(), 'editCompany')) {
                throw new Http403Exception('Permission error');
            }

            $this->db->begin();

            $this->imageService->setCompanyLogotype($company, $this->request->getUploadedFiles()[0]);

            $this->db->commit();
        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                case CompanyService::ERROR_UNABLE_CHANGE_COMPANY:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('All ok');
    }

    /**
     * Делает указанного пользователя менеджером компании
     *
     * @method POST
     *
     * @params user_id, company_id
     *
     * @return int account_id
     */
    public function setManagerAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['user_id'] = $inputData->user_id;
        $data['company_id'] = $inputData->company_id;

        //validation
        if (empty(trim($data['user_id']))) {
            $errors['user_id'] = 'Missing required parameter "user_id"';
        }

        if (empty(trim($data['company_id']))) {
            $errors['company_id'] = 'Missing required parameter "company_id"';
        }

        if (!is_null($errors)) {
            $errors['errors'] = true;
            $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $company = $this->companyService->getCompanyById($data['company_id']);

            if (!Accounts::checkUserHavePermissionToCompany($userId, $company->getCompanyId(), 'addManager')) {
                throw new Http403Exception('Permission error');
            }

            $data['company_role_id'] = CompanyRole::ROLE_MANAGER_ID;

            $account_id = $this->accountService->createAccount($data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_UNABLE_CREATE_ACCOUNT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Manager wa successfully added', ['account_id' => $account_id]);
    }

    /**
     * Удаляет пользователя из менеджеров компании
     *
     * @method DELETE
     *
     * @param $user_id
     * @param $company_id
     *
     * @return string message. Just message.
     */
    public function deleteManagerAction($company_id, $user_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $company = $this->companyService->getCompanyById($company_id);

            if (!Accounts::checkUserHavePermissionToCompany($userId, $company->getCompanyId(), 'deleteManager')) {
                throw new Http403Exception('Permission error');
            }

            $account = $this->accountService->getAccountByIds($company_id, $user_id);

            $this->accountService->deleteAccount($account);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_UNABLE_DELETE_ACCOUNT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Manager wa successfully deleted');
    }

    /**
     * Восстанавливает компанию
     *
     * @method POST
     *
     * @params companyId
     *
     * @return string - json array - объект Status - результат операции
     */
    public function restoreCompanyAction()
    {
        //TODO Необходимо сделать восстановление компании, когда будут переделаны все основные контроллеры (и модели).
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirst(['companyid = :companyId:',
                'bind' => ['companyId' => $this->request->getPost('companyId')]], false);

            if (!$company || !Companies::checkUserHavePermission($userId, $company->getCompanyId(), 'restoreCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$company->restore()) {
                $errors = [];
                foreach ($company->getMessages() as $message) {
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
                    "status" => STATUS_OK,
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /*public function deleteCompanyTestAction($companyId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if (!Companies::checkUserHavePermission($userId, $companyId, 'deleteCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $company = Companies::findFirstByCompanyid($companyId);
            if (!$company->delete(true)) {
                $errors = [];
                foreach ($company->getMessages() as $message) {
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
    }*/

    /**
     * Возвращает публичную информацию о компании.
     * Публичный доступ
     *
     * @method GET
     *
     * @param $company_id
     * @return string - json array компаний
     */
    public function getCompanyInfoAction($company_id)
    {
        try {
            $company = $this->companyService->getCompanyById($company_id);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return Companies::handleCompanyFromArray([$company->toArray()]);
    }
}
