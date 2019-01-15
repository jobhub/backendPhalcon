<?php

namespace App\Controllers;

use App\Controllers\AbstractController;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\AbstractService;
use App\Services\ServiceException;
use App\Services\UserService;

class UserController extends AbstractController {

    /**
     * Adding user
     */
    public function addAction() {
        /** Init Block * */
        $errors = [];
        $data = [];
        /** End Init Block * */
        /** Validation Block * */
        $data['email'] = $this->request->getPost('email');
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email addresse';
        } else
        if (!is_null($this->userService->findOnByEmail($data['email']))) {
            $errors['email'] = 'Email addresse already used ';
        }

        $data['password'] = $this->request->getPost('password');
        if (!is_string($data['password']) || !preg_match('/^[A-z0-9_-]{6,18}$/', $data['password'])) {
            $errors['password'] = 'Password must consist of 6-18 latin symbols, numbers or \'-\' and \'_\' symbols';
        }

        $data['first_name'] = $this->request->getPost('first_name');
        if ((empty(trim($data['first_name'])))) {
            $errors['first_name'] = 'String expected';
        }

        $data['last_name'] = $this->request->getPost('last_name');
        if (empty(trim($data['last_name']))) {
            $errors['last_name'] = 'String expected';
    }

        $data['status'] = $this->request->getPost('status');
        if (empty(trim($data['status']))) {
            $data['status'] = 'He there i\'am a new user';
        }

        if ($errors) {
            $errors['errors'] = true;
            $exception = new Http400Exception(_('Input parameters validation error'), self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }
        /** End Validation Block * */
        /** Passing to business logic and preparing the response * */
        try {
            $this->userService->createUser($data);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AbstractService::ERROR_ALREADY_EXISTS:
                case UserService::ERROR_UNABLE_CREATE_USER:
                    throw new Http422Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return parent::chatResponce('User successfull created');
        /** End Passing to business logic and preparing the response  * */
    }

    public function loginAction() {
        $data = [];
        $jsonData = $this->request->getJsonRawBody();
        $data["email"] = $jsonData->email;
        //$data["password"] = $jsonData->password;
        $user = \App\Auth\UserEmailAccountType::login($data);
        $payload = [
            'sub' => $user->userid,
            'email' => $user->email,
            //'username' => $user->username,
            'role' => 'admin',
            'iat' => time(),
        ];
        $token = $this->auth->make($payload);
        $response = ['token' => $token];
        return $response;
    }

    /**
     * Returns user list
     *
     * @return array
     */
    public function getUserListAction($id) {
        try {

            $userList = $this->userService->getUserList('' . $id);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return $userList;
    }

    /**
     * Get existing user by email
     *
     * @param string $userId
     */
    public function getUserByAction($email) {
        try {
            $user = $this->userService->findOnByEmail('' . $email);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return $user;
    }

    /**
     * Get user discussions
     *
     * @return  string $userId
     */
    public function getUserPrivateChatAction() {
        try {
            $user_id = $this->getUserId();
            $discussions = $this->privateChatService->getUserPublicChats($user_id);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return $discussions;
    }

    /**
     * Get user spam discussions
     *
     * @return  string $userId
     */
    public function getUserSpamPrivateChatAction() {
        try {
            $user_id = $this->getUserId();
            $discussions = $this->privateChatService->getUserPublicChats($user_id,true);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return $discussions;
    }

    /**
     * Get user hidden discussions
     *
     * @return  array $discussions
     */
    public function getUserHiddenPrivateChatAction() {
        try {
            $user_id = $this->getUserId();
            $discussions = $this->privateChatService->getUserPublicChats($user_id, false, true);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return $discussions;
    }

}
