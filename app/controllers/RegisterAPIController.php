<?php

namespace App\Controllers;

use App\Services\AbstractService;
use App\Services\ResetPasswordService;
use Dmkit\Phalcon\Auth\Auth;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Libs\SupportClass;
use App\Libs\PHPMailerApp;

//Models
use App\Models\Phones;
use App\Models\Accounts;
use App\Models\Users;
use App\Models\ActivationCodes;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

//Services
use App\Services\UserService;
use App\Services\UserInfoService;
use App\Services\AccountService;
use App\Services\AuthService;

/**
 * Class RegisterAPIController
 * Контроллер для регистрации пользователей.
 * Содержит методы для регистрации пользователя и работы с активационным кодом.
 * На данный момент это касается только активационного кода через email.
 */
class RegisterAPIController extends AbstractController
{
    /**
     * Регистрирует пользователя в системе
     *
     * @access public
     * @method POST
     *
     * @params login, password,
     *
     * @return array. Если все прошло успешно - [status, token, lifetime (время, после которого токен будет недействительным)],
     * иначе [status,errors => <массив сообщений об ошибках>]
     */
    public function indexAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        if ($data == null) {
            $exception = new Http400Exception(_('Can\'t parse input json array'), self::ERROR_INVALID_REQUEST);
            throw $exception;
        }

        $checking = $this->authService->checkLogin($data['login']);

        if ($checking == AuthService::LOGIN_INCORRECT) {
            $errors['login'] = 'Invalid login';
        } elseif ($checking == AuthService::LOGIN_EXISTS) {
            $errors['login'] = 'User with same login already exists';
        }

        if (strlen($data['password']) < 6) {
            $errors['password'] = 'password too few';
        }

        if (!is_null($errors)) {
            $errors['errors'] = true;
            $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }

        SupportClass::writeMessageInLogFile("Проверил юзера");

        $this->db->begin();

        try {
            $resultUser = $this->userService->createUser($data);

            $result = $this->accountService->createAccount(['user_id' => $resultUser->getUserId()]);

            SupportClass::writeMessageInLogFile("Дошел до создания сессии");
            $tokens = $this->authService->createSession($resultUser);

            SupportClass::writeMessageInLogFile("Дошел до отправки кода активации");
           // $this->authService->sendActivationCode($resultUser);
            SupportClass::writeMessageInLogFile("Отправил код активации");

            $tokens['role'] = $resultUser->getRole();
            $this->db->commit();
        } /*catch(ServiceExtendedException $e){
            switch ($e->getCode()) {
                case AuthService::ERROR_NO_TIME_TO_RESEND:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }*/ catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_UNABLE_CREATE_ACCOUNT:
                case UserService::ERROR_UNABLE_CREATE_USER:
                case AuthService::ERROR_UNABLE_SEND_TO_MAIL:
                case AuthService::ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AuthService::ERROR_USER_ALREADY_ACTIVATED:
                case AuthService::ERROR_USER_DO_NOT_EXISTS:
                    throw new Http500Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('All ok', $tokens);
    }

    /**
     * Проверяет, подходит ли логин для регистрации нового пользователя
     *
     * @access public
     * @method POST
     *
     * @params login
     *
     * @return string json array Status
     */
    public function checkLoginAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        $checking = $this->authService->checkLogin($data['login']);

        if ($checking == AuthService::LOGIN_INCORRECT) {
            $errors['login'] = 'Invalid login';
        } elseif ($checking == AuthService::LOGIN_EXISTS) {
            $errors['login'] = 'User with same login already exists';
        }

        if (!is_null($errors)) {
            $errors['errors'] = true;
            $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }

        return self::successResponse('All ok');
    }

    /**
     * Активирует пользователя.
     *
     * @access defective
     *
     * @method POST
     *
     * @params (обязательные) firstname, lastname, male
     * @params (Необязательные) patronymic, birthday, about (много текста о себе),
     * @return string - json array Status
     */
    public function confirmAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        $user = Users::findFirst(['userid = :userId:', 'bind' =>
            [
                'userId' => $userId
            ]
        ]);

        if (!$user) {
            /*$response->setJsonContent(
                [
                    "status" => STATUS_UNRESOLVED_ERROR,
                    "errors" => ['Пользователь не создан']
                ]
            );
            return $response;*/
            throw new Http500Exception('Пользователь не создан');
        }

        /*if ($user->getActivated()) {
            throw new Http422Exception('Пользователь уже активирован');
        }*/

        /*$activationCode = ActivationCodes::findFirstByUserid($user->getUserId());

        if (!$activationCode || (strtotime(time() - $activationCode->getTime()) > 3600)) {
            throw new Http400Exception('Wrong activation code');
        }*/

        $this->db->begin();
        try {
            $data['userId'] = $user->getUserId();
            $this->userInfoService->createUserInfo($data);

            $this->userInfoService->createSettings($user->getUserId());
            $this->userService->setNewRoleForUser($user, ROLE_USER);
        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case UserInfoService::ERROR_UNABLE_CREATE_USER_INFO:
                case UserInfoService::ERROR_UNABLE_CREATE_SETTINGS:
                case UserService::ERROR_UNABLE_CHANGE_USER:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        $this->db->commit();

        return self::chatResponce('User was successfully confirmed');
    }

    /**
     * Подтверждает, что пользователь - владелец (пока только) почты.
     *
     * @access public
     *
     * @method POST
     *
     * @params activation_code, login
     *
     * @return Status
     */
    public function activateLinkAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        if (empty(trim($data['login']))) {
            $errors['login'] = 'Required login';
        }

        $user = Users::findByLogin($data['login']);

        if (!$user) {
            $errors['login'] = 'User with this login don\'t exists';
        }

        if ($user->getActivated()) {
            $errors['login'] = 'User already activate';
        }

        $checking = $this->userService->checkActivationCode($data['activation_code'], $user->getUserId());
        if ($checking == UserService::WRONG_ACTIVATION_CODE)
            $errors['activation_code'] = 'Wrong activation code';

        if (!is_null($errors)) {
            $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }


        $this->db->begin();
        try {
            if ($checking == UserService::RIGHT_ACTIVATION_CODE) {
                $this->userService->deleteActivationCode($user->getUserId());
                $this->userService->changeUser($user, ['role'=>ROLE_USER_DEFECTIVE,'activated'=>true]);
            } elseif ($checking == UserService::RIGHT_DEACTIVATION_CODE) {
                $this->userService->deleteUser($user->getUserId());
            } else {
                throw new ServiceException(_('Internal Server Error'));
            }

            if ($this->session->get('auth') != null) {
                $res = $this->authService->createSession($user);
            }

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case UserService::ERROR_UNABLE_DELETE_ACTIVATION_CODE:
                case UserService::ERROR_UNABLE_DELETE_USER:
                case UserService::ERROR_UNABLE_CHANGE_USER:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }


        $this->db->commit();

        return self::successResponse('User was successfully activated', $res);
    }

    /**
     * Отправляет активационный код пользователю. Пока только на почту.
     * @access public, но пользователь должен быть авторизован
     * @method POST
     *
     * @return Response - json array в формате Status
     */
    public function getActivationCodeAction()
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        if (is_null($auth)) {
            throw new Http403Exception();
        }

        $user = Users::findFirstByUserid($userId);

        if (!$user) {
            throw new Http403Exception();
        }

        try {
            $this->authService->sendActivationCode($user);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AuthService::ERROR_UNABLE_SEND_TO_MAIL:
                case AuthService::ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE:
                case AuthService::ERROR_NO_TIME_TO_RESEND:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AuthService::ERROR_USER_ALREADY_ACTIVATED:
                case AuthService::ERROR_USER_DO_NOT_EXISTS:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return self::successResponse('Activation code successfully sent');
    }

    /**
     * Отправляет пользователю код для сброса пароля
     * @access public
     *
     * @method POST
     *
     * @params login
     *
     * @return Status
     */
    public function getResetPasswordCodeAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        $user = Users::findByLogin($data['login']);

        if (!$user || $user == null) {
            $errors['login'] = 'Invalid login';
        }

        if (!is_null($errors)) {
            $errors['errors'] = true;
            $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }

        //Пока, если код существует, то просто перезаписывается
        try {
            $this->resetPasswordService->sendPasswordResetCode($user);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AbstractService::ERROR_UNABLE_SEND_TO_MAIL:
                case ResetPasswordService::ERROR_UNABLE_TO_CREATE_RESET_PASSWORD_CODE:
                case ResetPasswordService::ERROR_NO_TIME_TO_RESEND:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Code for reset password successfully sent');
    }

    /**
     * Проверяет, верен ли код для сброса пароля
     * @access public
     *
     * @method POST
     *
     * @params login
     * @params reset_code
     *
     * @return Status
     */
    public function checkResetPasswordCodeAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        try {
            $user = $this->userService->getUserByLogin($data['login']);

            $checking = $this->resetPasswordService->checkResetPasswordCode($user,$data['reset_code']);

            if($checking == ResetPasswordService::RIGHT_DEACTIVATE_PASSWORD_RESET_CODE){
                $this->resetPasswordService->deletePasswordResetCode($user->getUserId());
                return self::successResponse('Request to change password successfully canceled');
            }

            if($checking == ResetPasswordService::WRONG_PASSWORD_RESET_CODE){
                $exception = new Http400Exception(_('Invalid some parameters'));
                $errors['errors'] = true;
                $errors['reset_code'] = 'Invalid reset code';
                throw $exception->addErrorDetails($errors);
            }

            if($checking == ResetPasswordService::RIGHT_PASSWORD_RESET_CODE){
                return self::successResponse('Code is valid');
            }

            throw new Http500Exception(_('Internal Server Error'));

        } catch(ServiceExtendedException $e){
            switch ($e->getCode()) {
                case ResetPasswordService::ERROR_UNABLE_DELETE_RESET_PASSWORD_CODE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch(ServiceException $e){
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Меняет пароль, если активационный код верен
     * @access public
     *
     * @method POST
     *
     * @params login
     * @params resetcode
     * @params password
     *
     * @return string - json array Status
     */
    public function changePasswordAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        try {
            $user = $this->userService->getUserByLogin($data['login']);

            $checking = $this->resetPasswordService->checkResetPasswordCode($user,$data['reset_code']);

            if($checking == ResetPasswordService::WRONG_PASSWORD_RESET_CODE){
                $exception = new Http400Exception(_('Invalid some parameters'));
                $errors['errors'] = true;
                $errors['reset_code'] = 'Invalid reset code';
                throw $exception->addErrorDetails($errors);
            }

            if($checking == ResetPasswordService::RIGHT_PASSWORD_RESET_CODE){
                $this->userService->setPasswordForUser($user,$data['password']);
                $this->resetPasswordService->deletePasswordResetCode($user->getUserId());
                return self::successResponse('Password was changed successfully');
            }

            throw new Http500Exception(_('Internal Server Error'));
        } catch(ServiceExtendedException $e){
            switch ($e->getCode()) {
                case UserService::ERROR_UNABLE_CHANGE_USER:
                case ResetPasswordService::ERROR_UNABLE_DELETE_RESET_PASSWORD_CODE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch(ServiceException $e){
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }
}

