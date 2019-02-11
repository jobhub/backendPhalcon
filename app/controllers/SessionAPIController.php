<?php

namespace App\Controllers;

use App\Models\Accounts;
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
            foreach ($accounts as $account){
                $accountsRes[] = ['account'=> $account->toArray(),
                    'info'=>$account->getUserInfomations()->toArray()];
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
     * @return string - json array в формате Status
     */
    public function authWithSocialAction()
    {
        if ($this->request->isGet()) {
            $ulogin = new Auth(array(
                'fields' => 'first_name,last_name,email,phone,sex',
                'url' => '/sessionAPI/authWithSocial',
                'optional' => 'pdate,photo_big,city,country',
                'type' => 'panel',
            ));
            return $ulogin->getForm();
        } else if ($this->request->isPost()) {
            $ulogin = new Auth(array(
                'fields' => 'first_name,last_name,email,phone,sex',
                'url' => '/sessionAPI/authWithSocial',
                'optional' => 'pdate,photo_big,city,country',
                'type' => 'panel',
            ));
            if ($ulogin->isAuthorised()) {
                $response = new Response();
                $ulogin->logout();
                $userSocial = Userssocial::findByIdentity($ulogin->getUser()['network'], $ulogin->getUser()['identity']);

                if (!$userSocial) {

                    //Регистрируем
                    $phone = $ulogin->getUser()['phone'];
                    $email = $ulogin->getUser()['email'];

                    $phoneObj = Phones::findFirstByPhone(Phones::formatPhone($phone));

                    $user = Users::findFirst(
                        [
                            "(email = :email: OR phoneid = :phoneId:)",
                            "bind" => [
                                "email" => $email,
                                "phoneId" => $phoneObj ? $phoneObj->getPhoneId() : null
                            ]
                        ]
                    );

                    if ($user != false) {
                        $response->setJsonContent(
                            [
                                "status" => STATUS_ALREADY_EXISTS,
                                'errors' => ['Пользователь с таким телефоном/email-ом уже зарегистрирован']
                            ]
                        );
                        return $response;
                    }

                    $this->db->begin();

                    $user = new Users();

                    if ($phone != null) {
                        //Добавление телефона, если есть
                        $phoneObject = new Phones();
                        $phoneObject->setPhone($phone);

                        if ($phoneObject->save()) {
                            $user->setPhoneId($phoneObject->getPhoneId());
                        }
                    }

                    $user->setEmail($email);
                    $user->setIsSocial(true);
                    $user->setRole("User");

                    if ($user->save() == false) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($user->getMessages() as $message) {
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

                    $userInfo = new Userinfo();
                    $userInfo->setUserId($user->getUserId());
                    $userInfo->setFirstname($ulogin->getUser()['first_name']);
                    $userInfo->setLastname($ulogin->getUser()['last_name']);
                    $userInfo->setMale(($ulogin->getUser()['sex'] - 1) >= 0 ? $ulogin->getUser()['sex'] - 1 : 1);
                    if (isset($ulogin->getUser()['country']) && isset($ulogin->getUser()['city']))
                        $userInfo->setAddress($ulogin->getUser()['country'] . ' ' . $ulogin->getUser()['city']);

                    if ($userInfo->save() == false) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($userInfo->getMessages() as $message) {
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

                    $setting = new Settings();
                    $setting->setUserId($user->getUserId());

                    if ($setting->save() == false) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($setting->getMessages() as $message) {
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

                    $userSocial = new Userssocial();
                    $userSocial->setUserId($user->getUserId());
                    $userSocial->setNetwork($ulogin->getUser()['network']);
                    $userSocial->setIdentity($ulogin->getUser()['identity']);
                    $userSocial->setProfile($ulogin->getUser()['profile']);

                    if ($userSocial->save() == false) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($userSocial->getMessages() as $message) {
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

                    $this->db->commit();

                    $this->SessionAPI->_registerSession($user);

                    $response->setJsonContent(
                        [
                            "status" => STATUS_OK
                        ]
                    );
                    return $response;
                }

                //Авторизуем
                $this->SessionAPI->_registerSession($userSocial->users);

                $response->setJsonContent([
                    'status' => STATUS_OK
                ]);
                return $response;
            } else {
                $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

                throw $exception;
            }
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}