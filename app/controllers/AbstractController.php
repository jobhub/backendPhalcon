<?php

namespace App\Controllers;

use Phalcon\DI\FactoryDefault as DI;

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

    public function successPaginationResponse($msg, $data = null, $pagination = null)
    {
        if(!is_null($pagination)) {
            if (is_integer($pagination))
                return ['success' => true, 'msg' => $msg, 'pagination' => ['total' => $pagination], 'data' => $data];
            else {
                if (isset($pagination['total'])) {
                    return ['success' => true, 'msg' => $msg, 'pagination' => ['total' => $pagination['total']], 'data' => $data];
                }
            }
        }
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

    public static function getAccountId(){
        $di = DI::getDefault();
        return $di->getSession()->get('accountId');
    }
}