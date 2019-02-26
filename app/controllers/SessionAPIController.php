<?php

namespace App\Controllers;

use App\Libs\SimpleULogin;
use App\Libs\SocialAuther\Adapter\Facebook;
use App\Libs\SocialAuther\Adapter\Google;
use App\Models\Accounts;
use App\Models\UsersSocial;
use App\Services\AbstractService;
use App\Services\AccountService;
use App\Services\ImageService;
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
    public function authWithSocialAction($social_net = null)
    {
        try {
            if ($this->request->isGet()) {
                if (!isset($_GET['code']) && !isset($_GET['error'])) {
                    switch($social_net) {
                        case 'vk': {
                            $vkAdapterConfig = $this->config['social']['vk'];
                            $adapter = new Vk($vkAdapterConfig);
                            break;
                        }
                        case 'facebook':{
                            $configInfoNet = $this->config['social']['facebook'];
                            $adapter = new Facebook($configInfoNet);
                            break;
                        }
                        case 'google':{
                            $configInfoNet = $this->config['social']['google'];
                            $adapter = new Google($configInfoNet);
                            break;
                        }
                    }
                    return ['url' => $adapter->getAuthUrl()];
                } else {
                    return $_GET;
                }
            }

            $data = json_decode($this->request->getRawBody(), true);

            $this->db->begin();

            switch ($data['provider']) {
                case 'vk': {
                    $vkAdapterConfig = $this->config['social']['vk'];
                    $vkAdapter = new Vk($vkAdapterConfig);
                    $auther = new SocialAuther($vkAdapter);
                    break;
                }
                case 'facebook': {
                    $vkAdapterConfig = $this->config['social']['facebook'];
                    $fbAdapter = new Facebook($vkAdapterConfig);
                    $auther = new SocialAuther($fbAdapter);
                    break;
                }
                case 'google': {
                    $vkAdapterConfig = $this->config['social']['google'];
                    $googleAdapter = new Google($vkAdapterConfig);
                    $auther = new SocialAuther($googleAdapter);
                    break;
                }
                default:
                    return null;
            }

            SupportClass::writeMessageInLogFile("Перед пыткой вызвать authenticate");
            $result = $auther->authenticate($data['code']);
            $strRes = var_export($result,true);
            SupportClass::writeMessageInLogFile("result = " . $strRes);

            if(!$result){
                throw new Http400Exception('Unable authenticate in social net',SocialNetService::ERROR_UNABLE_AUTHENTICATE_IN_NET);
            }

            $userFromSocialNet = $auther->getUser();

            $strUser = var_export($userFromSocialNet,true);

            SupportClass::writeMessageInLogFile("Полученные данные о юзере - ".$strUser);

            $userSocial = UsersSocial::findByIdentity($userFromSocialNet['network'], $userFromSocialNet['identity']);

            if (!$userSocial) {

                if(empty($userFromSocialNet['first_name'])){
                    $userFromSocialNet['first_name'] = $data['first_name'];
                }

                if(empty($userFromSocialNet['last_name'])){
                    $userFromSocialNet['last_name'] = $data['last_name'];
                }

                if(empty($userFromSocialNet['city_id'])){
                    $userFromSocialNet['city_id'] = $data['city_id'];
                }

                if(empty($userFromSocialNet['male'])){
                    $userFromSocialNet['male'] = $data['male'];
                }

                if(empty($userFromSocialNet['first_name'])){
                    $errors['first_name'] = 'Missing require field "first_name"';
                }

                if(empty($userFromSocialNet['last_name'])){
                    $errors['last_name'] = 'Missing require field "last_name"';;
                }

                if(empty($userFromSocialNet['city_id'])){
                    $errors['city_id'] = 'Missing require field "city_id"';;
                }

                if(empty($userFromSocialNet['male'])){
                    $errors['male'] = 'Missing require field "male"';
                }

                $user = $this->socialNetService->registerUserByNet($userFromSocialNet);

                $strUser = var_export($user->getUserId(),true);
                SupportClass::writeMessageInLogFile("user from register by net - ".$strUser);

                $tokens = $this->authService->createSession($user);

                SupportClass::writeMessageInLogFile("got token token");
                $this->db->commit();
                SupportClass::writeMessageInLogFile("call db->commit");
                return self::chatResponce('User was successfully registered', $tokens);
            }

            //Авторизуем
            /*$strUserSocial = var_export($userSocial,true);
            SupportClass::writeMessageInLogFile("Пользователь уже существует - ".$strUserSocial);*/


            /*$strUser = var_export($userSocial->users,true);*/
            SupportClass::writeMessageInLogFile("user_id в userSocial - ".$userSocial->getUserId());


            $user = Users::findFirstByUserId($userSocial->getUserId());

            if(!$user)
                SupportClass::writeMessageInLogFile("user по id не найден");

            $user = $this->userService->getUserById($userSocial->getUserId());

            $tokens = $this->authService->createSession($user);
            $this->db->commit();

            return self::chatResponce('User was successfully authorized', $tokens);
        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case UserService::ERROR_UNABLE_CREATE_USER:
                case AccountService::ERROR_UNABLE_CREATE_ACCOUNT:
                case UserInfoService::ERROR_UNABLE_CREATE_USER_INFO:
                case UserInfoService::ERROR_UNABLE_CHANGE_USER_INFO:
                case UserInfoService::ERROR_UNABLE_CREATE_SETTINGS:
                case UserService::ERROR_UNABLE_CHANGE_USER:
                case SocialNetService::ERROR_UNABLE_CREATE_USER_SOCIAL:
                case SocialNetService::ERROR_INFORMATION_FROM_NET_NOT_ENOUGH:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception($e->getMessage()/*_('Internal Server Error')*/, $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                case AuthService::ERROR_INCORRECT_PASSWORD:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception($e->getMessage()/*_('Internal Server Error')*/, $e->getCode(), $e);
            }
        }
    }

    public function testingSavingImageToUserProfileAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        if (empty($data['uri_to_photo'])) {
            $errors['uri_to_photo'] = 'Missing required parameter \'uri_to_photo\'';
        }

        if (!is_null($errors)) {
            $errors['errors'] = true;
            $exception = new Http400Exception('Invalid some parameters', self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }

        try {

            $resultName = '';

            $nameStart = false;
            for($i = strlen($data['uri_to_photo'])-1;$i>0; $i--){
                if($nameStart){
                    if($data['uri_to_photo'][$i]!='/'){
                        $resultName =$data['uri_to_photo'][$i] . $resultName;
                    } else
                        break;
                } elseif($data['uri_to_photo'][$i]=='?'){
                    $nameStart = true;
                }
            }

            $userId = self::getUserId();

            $user = $this->userService->getUserById($userId);
            $userInfo = $this->userInfoService->getUserInfoById($userId);

            $this->userInfoService->savePhotoForUserByURL($user,$userInfo,$data['uri_to_photo'],$resultName);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                case UserInfoService::ERROR_USER_INFO_NOT_FOUND:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception($e->getMessage(), $e->getCode(), $e);
            }
        }
        return self::successResponse('Photo successfully changed');
    }
}