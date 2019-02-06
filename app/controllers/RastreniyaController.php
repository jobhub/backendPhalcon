<?php

namespace App\Controllers;

use App\Controllers\AbstractController;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Models\Accounts;
use App\Services\AbstractService;
use App\Services\ServiceException;
use App\Services\UserService;

class RastreniyaController extends AbstractController {


    public function newAction(){
        $user_id = $this->getUserid();
        $data = json_decode($this->request->getRawBody(), true);
        try {
           $rast = $this->rastreniyaService->create($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('Rastreniya successfully created', $rast);
    }

    public function getAction($params){
        $user_id = $this->getUserid();
        $data = [];
        $data['page'] = $this->request->getQuery('page', $data);
        try {
           $rasts = $this->rastreniyaService->getRasts($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return $rasts;
    }

    public function putAction(){
        $data = json_decode($this->request->getRawBody(), true);
        $data["user_id"] = $this->getUserid();
        $action = $data['action'];
        try {
            if(in_array($action, ["deleteRast", "updateRast"]) && method_exists($this->rastreniyaService, $action))
                $response = $this->rastreniyaService->$action($data, true); // using same message service with private chat
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

    public function noticeAction(){
        $data = json_decode($this->request->getRawBody(), true);
        $data["user_id"] = $this->getUserid();;
        $action = $data['action'];
        try {
            if(in_array($action, ["likeRast","dislikeRast"]) && method_exists($this->rastreniyaService, $action))
                $response = $this->rastreniyaService->$action($data);
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

    public function responseAction(){
        $data = json_decode($this->request->getRawBody(), true);
        $data["user_id"] = $this->getUserid();
        $action = $data['action'];
        try {
            if(in_array($action, ["newResponse","getResponses","deleteResponses","updateResponse"]) && method_exists($this->rastreniyaService, $action))
                $response = $this->rastreniyaService->$action($data);
            else
            {
                throw new Http400Exception(_('Action not found'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('Successfully done', $response);
    }

    public function mainAction(){
        $data = json_decode($this->request->getRawBody(), true);
        $data["user_id"] = $this->getUserid();;
        $action = $data['action'];
        try {
            if(method_exists($this->rastreniyaService, $action))
                    $response = $this->rastreniyaService->$action($data); // using same message service with private chat
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