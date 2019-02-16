<?php

namespace App\Controllers;

use App\Libs\SimpleULogin;
use App\Models\Accounts;
use App\Models\UsersSocial;
use App\Services\AccountService;
use App\Services\SocialNetService;
use App\Services\UserInfoService;
use Phalcon\Http\Client\Exception;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

use App\Libs\SupportClass;

use App\Models\Phones;
use App\Models\Accesstokens;
use App\Models\Users;

use App\Services\UserService;
use App\Services\AuthService;

use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http404Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;

use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use ULogin\Auth;

/**
 * Class SessionAPIController
 * Контроллер, предназначеный для авторизации пользователей и содержащий методы сязанные с этим процессом
 * А именно, методы для авторизации пользователя, разрыва сессии, получение роли текущего пользователя
 * и авторизация через соц. сеть (которая по совместительству и регистрация).
 */
class SessionAPIController extends AbstractController
{
    /**
     * Разрывает сессию пользователя
     * @method POST
     *
     * @return string - json array Status
     */
    /*public function endAction()
    {
        return $this->destroySession();
    }*/

    /**
     * Выдает текущую роль пользователя.
     * @access public
     * @method GET
     */
    public function getCurrentRoleAction()
    {
        $auth = $this->session->get('auth');

        try {
            if ($auth == null) {
                $role = ROLE_GUEST;
            } else {
                $userId = $auth['id'];

                $user = $this->userService->getUserById($userId);

                $role = $user->getRole();
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('', ['role' => $role]);
    }

    /**
     * Возвращает аккаунты текущего пользователя
     *
     * @access private
     *
     * @method GET
     *
     * @return array
     */
    public function getAccountsAction()
    {
        try {
            $userId = self::getUserId();

            $accounts = Accounts::findAccountsByUser($userId);

            $accountsRes = [];
            foreach ($accounts as $account) {
                $accountsRes[] = ['account' => $account->toArray(),
                    'info' => $account->getUserInfomations()->toArray()];
            }

        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return $accountsRes;
    }

    /**
     * Авторизует пользователя в системе
     *
     * @method POST
     * @params login (это может быть его email или номер телефона), password
     * @return string json array [status, allForUser => [user, userinfo, settings], token, lifetime]
     */
    public function indexAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        if (empty($data['login'])) {
            $errors['login'] = 'Missing required parameter \'login\'';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Missing required parameter \'password\'';
        }

        if (!is_null($errors)) {
            $exception = new Http400Exception('Invalid some parameters', self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }

        try {
            $user = $this->userService->getUserByLogin($data['login']);
            SupportClass::writeMessageInLogFile('email пользователя ' . $user->getEmail());
            SupportClass::writeMessageInLogFile('Юзер найден в бд');
            $this->authService->checkPassword($user, $data['password']);
            $result = $this->authService->createSession($user);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                case AuthService::ERROR_INCORRECT_PASSWORD:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return self::successResponse('Successfully login', $result);
    }

    /**
     * Авторизация через соц. сеть
     * Должен автоматически вызываться компонентом uLogin.
     *
     * @method GET
     * @return array - json array в формате Status
     */
    public function authWithSocialAction()
    {
        try {
            if ($this->request->isGet()) {
                $ulogin = new SimpleULogin(array(
                    'fields' => 'first_name,last_name,email,phone,sex,city',
                    'url' => '/authorization/social',
                    'optional' => 'pdate,photo_big,country',
                    'type' => 'panel',
                ));
                return ['form' => $ulogin->getForm()];
            } else if ($this->request->isPost()) {
                $ulogin = new Auth(array(
                    'fields' => 'first_name,last_name,email,phone,sex,city',
                    'url' => '/authorization/social',
                    'optional' => 'pdate,photo_big,country',
                    'type' => 'panel',
                ));

                if (!$ulogin->isAuthorised()) {
                    throw new ServiceException('Не удалось авторизоваться через uLogin');
                }

                $ulogin->logout();

                $userFromULogin = $ulogin->getUser();

                $userSocial = UsersSocial::findByIdentity($userFromULogin['network'], $userFromULogin['identity']);

                if (!$userSocial) {

                    $user = $this->socialNetService->registerUserByNet($userFromULogin);

                    $tokens = $this->authService->createSession($user);
                    return self::chatResponce('User was successfully registered', $tokens);
                }

                //Авторизуем
                $tokens = $this->authService->createSession($userSocial->users);
                return self::chatResponce('User was successfully authorized', $tokens);
            }

            $exception = new Http404Exception(
                _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                new \Exception('URI not found: ' .
                    $this->request->getMethod() . ' ' . $this->request->getURI())
            );
            throw $exception;

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_UNABLE_CREATE_USER:
                case AccountService::ERROR_UNABLE_CREATE_ACCOUNT:
                case UserInfoService::ERROR_UNABLE_CREATE_USER_INFO:
                case UserInfoService::ERROR_UNABLE_CREATE_SETTINGS:
                case UserService::ERROR_UNABLE_CHANGE_USER:
                case SocialNetService::ERROR_UNABLE_CREATE_USER_SOCIAL:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                case AuthService::ERROR_INCORRECT_PASSWORD:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }
}