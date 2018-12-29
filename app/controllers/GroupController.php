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
