<?php

namespace App\Controllers;

use App\Libs\SupportClass;
use App\Models\CompanyRole;
use App\Services\CategoryService;
use App\Services\ConfirmService;
use App\Services\InviteService;
use App\Services\MarkerService;
use App\Services\NotificationService;
use App\Services\PhoneService;
use App\Services\PointService;
use App\Services\UserService;
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
use App\Services\AbstractService;

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

            $company = $this->companyService->createCompany($data);

            $account_id = $this->accountService->createAccount([
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

        return self::successResponse('Company was successfully created',
            [
                'company_id' => $company->getCompanyId(),
                'account_id' => $account_id
            ]);
    }

    /**
     * Создает бизнес аккаунт для указанного пользователя.
     * А именно, компанию и точку оказания услуг к ней.
     *
     * @access private
     *
     * @method POST
     *
     * @params confirm_code (!)
     * Для компании
     * @params category_id int - категория, в которой компания будет оказывать услуги
     * @params company_name (!) string - название компании
     *
     * Для точки оказания услуг
     * @params time string режим работы точки оказания услуг
     * @params latitude (!) double
     * @params longitude (!) double
     *
     * Спорно
     * @params website string
     * @params phones array [string] - массив номеров телефонов
     *
     */
    public function createBusinessAccount()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['category_id'] = $inputData->category_id;
        $data['company_name'] = $inputData->company_name;
        $data['time'] = $inputData->time;
        $data['website'] = $inputData->website;
        $data['phones'] = $inputData->phones;

        $data['latitude'] = $inputData->latitude;
        $data['longitude'] = $inputData->longitude;

        $data['confirm_code'] = $inputData->confirm_code;

        $this->db->begin();
        try {

            if (empty($data['company_name']))
                $errors['company_name'] = 'Missing required parameter "company_name"';

            if (empty($data['latitude']))
                $errors['latitude'] = 'Missing required parameter "latitude"';

            if (empty($data['longitude']))
                $errors['longitude'] = 'Missing required parameter "longitude"';

            if (empty($data['confirm_code']))
                $errors['confirm_code'] = 'Missing required parameter "confirm_code"';

            if (!is_null($errors)) {
                $exception = new Http400Exception(_('Invalid some parameters'));
                $errors['errors'] = true;
                throw $exception->addErrorDetails($errors);
            }

            $userId = self::getUserId();

            $user = $this->userService->getUserById($userId);

            $checking = $this->confirmService->checkConfirmCode($user, $data['confirm_code'],
                ConfirmService::TYPE_CREATE_COMPANY);

            if ($checking == ConfirmService::RIGHT_DEACTIVATE_CODE) {
                $this->confirmService->deleteConfirmCode($user->getUserId());
                return self::successResponse('Request to create company successfully canceled');
            }

            if ($checking == ConfirmService::WRONG_CONFIRM_CODE) {
                $errors['confirm_code'] = 'Invalid code';
            }

            if (!is_null($errors)) {
                $exception = new Http400Exception(_('Invalid some parameters'));
                $errors['errors'] = true;
                throw $exception->addErrorDetails($errors);
            }

            $company_data['name'] = $data['company_name'];
            $company_data['website'] = $data['website'];

            $company = $this->companyService->createCompany($company_data);

            $account_id = $this->accountService->createAccount([
                'user_id' => $userId,
                'company_id' => $company->getCompanyId(),
                'company_role_id' => CompanyRole::ROLE_OWNER_ID
            ]);

            $point_data['account_id'] = $account_id;
            $point_data['time'] = $data['time'];
            $point_data['name'] = $data['company_name'];
            $point_data['website'] = $data['website'];
            $point_data['longitude'] = $data['longitude'];
            $point_data['latitude'] = $data['latitude'];

            $tradePoint = $this->pointService->createPoint($point_data);

            if (isset($data['category_id']) && SupportClass::checkInteger($data['category_id']))
                $this->categoryService->linkCompanyWithCategory($data['category_id'], $company->getCompanyId());

            if (is_array($data['phones']))
                foreach ($data['phones'] as $phone) {
                    //Скорее всего, к компании
                    $this->phoneService->addPhoneToCompany($phone, $company->getCompanyId());
                }

            $this->confirmService->deleteConfirmCode($userId, ConfirmService::TYPE_CREATE_COMPANY);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case CompanyService::ERROR_UNABLE_CREATE_COMPANY:
                case AccountService::ERROR_UNABLE_CREATE_ACCOUNT:
                case CategoryService::ERROR_UNABlE_LINK_CATEGORY_WITH_COMPANY:
                case PointService::ERROR_UNABLE_CREATE_POINT:
                case MarkerService::ERROR_UNABLE_CREATE_MARKER:
                case PhoneService::ERROR_UNABLE_CREATE_PHONE:
                case PhoneService::ERROR_UNABLE_ADD_PHONE_TO_COMPANY:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case CategoryService::ERROR_CATEGORY_NOT_FOUND:
                    throw new Http422Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();

        return self::successResponse('Company was successfully created',
            [
                'company_id' => $company->getCompanyId(),
                'account_id' => $account_id
            ]);
    }

    /**
     * Отдает код для подтверждения создания бизнес-аккаунта
     *
     * @access private
     *
     * @method POST
     *
     */
    public function getConfirmCodeForCreateCompanyAction()
    {
        try {
            $userId = self::getUserId();
            $user = $this->userService->getUserById($userId);
            $this->confirmService->sendConfirmCode($user, ConfirmService::TYPE_CREATE_COMPANY);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AbstractService::ERROR_UNABLE_SEND_TO_MAIL:
                case ConfirmService::ERROR_UNABLE_TO_CREATE_CONFIRM_CODE:
                case ConfirmService::ERROR_NO_TIME_TO_RESEND:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http422Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Code for creation company successfully sent');
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
            $userId = self::getUserId();

            if (!Accounts::checkUserHavePermissionToCompany($userId, $company_id, 'deleteCompany')) {
                throw new Http403Exception('Permission error');
            }

            $company = $this->companyService->getCompanyById($company_id);

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
     * @params company_id
     *
     * @return string - json array - объект Status - результат операции
     */
    public function restoreCompanyAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['company_id'] = $inputData->company_id;

        try {
            $userId = self::getUserId();

            if (!Accounts::checkUserHavePermissionToCompany($userId, $data['company_id'], 'restoreCompany')) {
                throw new Http403Exception('Permission error');
            }

            $company = $this->companyService->getDeletedCompanyById($data['company_id']);

            $this->companyService->restoreCompany($company);

            $company = $this->companyService->getCompanyById($data['company_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_UNABLE_RESTORE_COMPANY:
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

        return self::successResponse('Company was successfully restored',
            SupportClass::getCertainColumnsFromArray($company->toArray(), Companies::publicColumns));
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
     * Возвращает информацию о компании для ЛК
     *
     * @access private
     *
     * @method GET
     *
     * @param $company_id
     * @param $account_id = null
     *
     * @return string - json array компаний
     */
    public function getCompanyInfoAction($company_id, $account_id = null)
    {
        try {
            $company = $this->companyService->getCompanyById($company_id);

            $currentUserId = self::getUserId();

            if ($account_id != null && SupportClass::checkInteger($account_id)) {
                if (!Accounts::checkUserHavePermission($currentUserId, $account_id, 'getNews')) {
                    throw new Http403Exception('Permission error');
                }

                $account = Accounts::findFirstById($account_id);
            } else {
                $account = Accounts::findForUserDefaultAccount($currentUserId);
            }

            if(!$account)
                $account = null;

        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return Companies::handleCompanyToProfile($company->toArray(),$account);
    }

    /**
     * Делает указанную коммпанию в том числе и магазином
     *
     * @access private
     * @method POST
     *
     * @param $company_id
     */
    public function setShop(){

    }
}
