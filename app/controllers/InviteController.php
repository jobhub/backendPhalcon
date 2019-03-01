<?php

namespace App\Controllers;

use App\Libs\SupportClass;
use App\Models\CompanyRole;
use App\Models\InvitesModel;
use App\Models\Users;
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
use App\Controllers\HttpExceptions\Http404Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Контроллер для работы с приглашениями.
 * Реализует методы для приглашения людей быть менеджерами и .
 */
class InviteController extends AbstractController
{
    /**
     * Приглашает указанного пользователя стать менеджером в компании.
     * Если есть user_id, email можно не указывать.
     *
     * @method POST
     *
     * @params user_id
     *
     * @params email
     *
     * @params company_id
     *
     * @return int account_id
     */
    public function inviteToBeManagerAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['user_id'] = $inputData->user_id;
        $data['company_id'] = $inputData->company_id;
        $data['email'] = $inputData->email;

        //validation
        if (empty(trim($data['user_id'])) && empty(trim($data['email']))) {
            $errors['user_id'] = 'Missing required parameter "user_id" or "email"';
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

            /*$data['company_role_id'] = CompanyRole::ROLE_MANAGER_ID;
            $account_id = $this->accountService->createAccount($data);*/

            $account = $this->accountService->getForUserDefaultAccount($userId);
            $invited_user_id = null;
            if(!empty($data['user_id'])){
                $invited_user_id = $data['user_id'];
            } else{
                $user = Users::findByLogin($data['email']);

                if($user){
                    $invited_user_id = $user->getUserId();
                }
            }

            if(!is_null($invited_user_id)) {
                $invite = InvitesModel::findInviteByData($data['user_id'],$data['company_id'],
                    InviteService::TYPE_INVITE_TO_BE_MANAGER);

                if($invite){
                    $exception = new Http400Exception('Пользователь уже приглашен');
                    throw $exception->addErrorDetails(['user_id'=>'User already invited']);
                }
                //Уже есть в системе
                $invite = $this->inviteService->createInvite(
                    [
                        'invited' => $invited_user_id,
                        'who_invited' => $account->getId(),
                        'where_invited' => $data['company_id']
                    ],
                    InviteService::TYPE_INVITE_TO_BE_MANAGER
                );
                $this->notificationService->sendNotification($invite, NotificationService::TYPE_INVITE_TO_BE_MANAGER);
            } else{
                //Еще не зарегистрирован
                $invite = InvitesModel::findInviteByData($data['email'],$data['company_id'],
                    InviteService::TYPE_INVITE_TO_REGISTER_AND_BE_MANAGER);

                if($invite){
                    $exception = new Http400Exception('Человек уже приглашен');
                    throw $exception->addErrorDetails(['email'=>'Person already invited']);
                }

                $invite = $this->inviteService->createInvite(
                    [
                        'invited' => $data['email'],
                        'who_invited' => $account->getId(),
                        'where_invited' => $data['company_id']
                    ],
                    InviteService::TYPE_INVITE_TO_REGISTER_AND_BE_MANAGER
                );

                $this->notificationService->sendNotification($company,
                    NotificationService::TYPE_COMPANY_INVITE_TO_REGISTER_AND_BE_MANAGER,['email'=>$data['email']]);
            }

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case InviteService::ERROR_UNABLE_CREATE_INVITE:
                case NotificationService::ERROR_UNABLE_SEND_NOTIFICATION:
                case AbstractService::ERROR_UNABLE_SEND_TO_MAIL:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case NotificationService::ERROR_INVALID_NOTIFICATION_TYPE:
                    throw new Http404Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Manager was invited successfully');
    }

    /**
     * Пользователь соглашается быть менеджером в компании
     *
     * @access private
     *
     * @method POST
     *
     * @params company_id
     */
    public function agreeToBeManager(){
        $inputData = $this->request->getJsonRawBody();
        $data['company_id'] = $inputData->company_id;

        //validation
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

            $invite = $this->inviteService->getInviteByData($userId,$data['company_id']);

            if(!$invite){
                $exception = new Http400Exception(_('User didn\'t invited'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $data['user_id'] = $userId;
            $data['company_role_id'] = CompanyRole::ROLE_MANAGER_ID;

            $account_id = $this->accountService->createAccount($data);

            $account = $this->accountService->getForUserDefaultAccount($userId);

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
                case InviteService::ERROR_INVITE_NOT_FOUND:
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Manager was added successfully');
    }
}
