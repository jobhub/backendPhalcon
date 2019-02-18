<?php

namespace App\Controllers;

use App\Libs\SimpleULogin;
use App\Models\Accounts;
use App\Models\UsersSocial;
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
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;

use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use ULogin\Auth;
use App\Libs\SocialAuther\Adapter\Vk;
use App\Libs\SocialAuther\SocialAuther;

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
                if (!isset($_GET['code'])) {
                    $vkAdapterConfig = $this->config['social']['vk'];
                    $vkAdapter = new Vk($vkAdapterConfig);
                    $auther = new SocialAuther($vkAdapter);

                    return ['url' => $vkAdapter->getAuthUrl()];
                } else {
                    return ['code' => $_GET['code']];
                }
            }

            $data = json_decode($this->request->getRawBody(), true);

            switch ($data['provider']) {
                case 'vk': {
                    $vkAdapterConfig = $this->config['social']['vk'];
                    $vkAdapter = new Vk($vkAdapterConfig);
                    $auther = new SocialAuther($vkAdapter);
                    break;
                }
                default:
                    return null;
            }

            SupportClass::writeMessageInLogFile("Перед пыткой вызвать authenticate");
            $result = $auther->authenticate($data['code']);
            SupportClass::writeMessageInLogFile("result = " . $result);

            SupportClass::writeMessageInLogFile("Перед пыткой вызвать var_dump на auther");
            //var_dump($auther);
            SupportClass::writeMessageInLogFile("Все норм, вызвал, вернул");
            return self::successResponse('All or or not', ['result'=>$result]);

            /*if (!$ulogin->isAuthorised()) {
                throw new ServiceException('Не удалось авторизоваться через uLogin');
            }*/

            $ulogin->logout();

            $userFromULogin = $ulogin->getUser();

            $userSocial = UsersSocial::findByIdentity($userFromULogin['network'], $userFromULogin['identity']);

            $this->db->begin();

            if (!$userSocial) {
                //Регистрируем
                $phone = $userFromULogin['phone'];
                $email = $userFromULogin['email'];

                if (isset($userFromULogin['phone'])) {
                    $data['login'] = $userFromULogin['phone'];
                } elseif ($userFromULogin['email']) {
                    $data['login'] = $userFromULogin['email'];
                } else {
                    throw new ServiceException('Нужен email или телефон');
                }

                $resultUser = $this->userService->createUser($data);

                $result = $this->accountService->createAccount(['user_id' => $resultUser->getUserId()]);

                $data_userinfo['user_id'] = $resultUser->getUserId();
                $data_userinfo['first_name'] = $userFromULogin['first_name'];
                $data_userinfo['last_name'] = $userFromULogin['last_name'];
                $data_userinfo['male'] = ($userFromULogin['sex'] - 1) >= 0 ? $userFromULogin['sex'] - 1 : 1;

                if (isset($userFromULogin['country']) && isset($userFromULogin['city']))
                    $data_userinfo['address'] = ($userFromULogin['country'] . ' ' . $userFromULogin['city']);

                $data_userinfo['city'] = $userFromULogin['city'];

                $this->userInfoService->createUserInfo($data);

                $this->userInfoService->createSettings($resultUser->getUserId());
                $this->userService->setNewRoleForUser($resultUser, ROLE_USER);

                $userSocial = new Userssocial();
                $userSocial->setUserId($resultUser->getUserId());
                $userSocial->setNetwork($userFromULogin['network']);
                $userSocial->setIdentity($userFromULogin['identity']);
                $userSocial->setProfile($userFromULogin['profile']);

                if ($userSocial->save() == false) {
                    $this->db->rollback();
                    SupportClass::getErrorsWithException($userSocial, 0, 'Не удалось создать user social object');
                }

                $this->db->commit();

                $tokens = $this->authService->createSession($resultUser);
                return self::chatResponce('User was successfully registered', $tokens);
            }

            //Авторизуем
            $tokens = $this->authService->createSession($userSocial->users);
        } catch (\Exception $e) {
            echo 'Exception message: '. $e;
        }

        return self::chatResponce('User was successfully authorized', $tokens);
    }
}