<?php

namespace App\Controllers;

/**
 * Class AbstractController
 *
 * @property \Phalcon\Http\Request $request
 * @property \Phalcon\Http\Response $htmlResponse
 * @property \Phalcon\Db\Adapter\Pdo\Postgresql $db
 * @property \Phalcon\Config $config
 * @property \App\Services\UsersService $usersService
 * @property \App\Models\Users $user
 */
abstract class AbstractController extends \Phalcon\DI\Injectable
{
    /**
     * Route not found. HTTP 404 Error
     */
    const ERROR_NOT_FOUND = 1;

    /**
     * Invalid Request. HTTP 400 Error.
     */
    const ERROR_INVALID_REQUEST = 2;

    /**
     * Global success response format
     */
    public function chatResponce($msg, $data = null)
    {
        return ['success' => true, 'msg' => $msg, 'data' => $data];
    }

    public function successResponse($msg, $data = null)
    {
        return ['success' => true, 'msg' => $msg, 'data' => $data];
    }

    public function isAuthorized(){
        $payload = $this->session->get('auth');
        return $payload!=null && $payload['id']!=null;
    }

    public function getUserId(){
        $payload = $this->session->get('auth');
        $current_user_id = $payload['id'];
        return $current_user_id;
    }

    public function setAccountId($accountId){
        $this->session->set('accountId',$accountId);
    }
}