<?php

namespace App\Controllers;

use App\Controllers\AbstractController;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\AbstractService;
use App\Services\ServiceException;
use App\Services\UserService;

class GroupController extends AbstractController {


    public function newAction(){
        $user_id = $this->getUserId();
        $data = json_decode($this->request->getRawBody(), true);
        try {
           $group = $this->groupService->create($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('Group successfully created', $group);
    }

    public function sendMessageAction(){
        $user_id = $this->getUserId();
        $data = json_decode($this->request->getRawBody(), true);
        $data["sender"] = $user_id;
        try {
            $this->messageService->sendMessage($data, true); // using same message service with private chat
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('Message successfully send');
    }

    public function setMessageToReadAction(){
        $user_id = $this->getUserId();
        $data = json_decode($this->request->getRawBody(), true);
        $data["sender"] = $user_id;
        try {
            $this->messageService->setAllMessageToReaded($data, true); // using same message service with private chat
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('Message successfully send');
    }

    public function messageAction(){
        $user_id = $this->getUserId();
        $data = json_decode($this->request->getRawBody(), true);
        $data["sender"] = $user_id;
        $action = $data['action'];
        try {
            if(method_exists($this->messageService, $action))
                $response = $this->messageService->$action($data, true); // using same message service with private chat
            else
            {
                throw new Http400Exception(_('Action not found'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        if(is_bool($response))
            return parent::successResponse('Successfully done');
        return $response;
    }

    public function mainAction(){
        $data = json_decode($this->request->getRawBody(), true);
        $data["user_id"] = $this->getUserId();;
        $action = $data['action'];
        try {
            if(method_exists($this->groupService, $action))
                    $response = $this->groupService->$action($data); // using same message service with private chat
            else
            {
                throw new Http400Exception(_('Action not found'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        if(is_bool($response))
            return parent::successResponse('Successfully done');
        return $response;
    }

    /**
     * Adding group
     */
    public function addAction() {
        /** Init Block * */
        $errors = [];
        $data = [];
        /** End Init Block * */
        /** Validation Block * */
        $data['name'] = $this->request->getPost('name');
        if (empty(trim($data['name']))) {
            $errors['name'] = 'String expected';
        }

        if ($errors) {
            $errors['errors'] = true;
            $exception = new Http400Exception(_('Input parameters validation error'), self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }
        /** End Validation Block * */
        /** Passing to business logic and preparing the response * */
        try {
            $this->userService->createGroup($data);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AbstractService::ERROR_ALREADY_EXISTS:
                case UserService::ERROR_UNABLE_CREATE_USER:
                    throw new Http422Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return parent::chatResponce('Group successfull created');
        /** End Passing to business logic and preparing the response  * */
    }

}
