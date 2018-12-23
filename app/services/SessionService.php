<?php

namespace App\Services;

use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Http\Response; //TODO Must be changed

use App\Libs\SupportClass;

use App\Models\Accesstokens;

/**
 * business logic for users
 *
 * Class UsersService
 */
class SessionService extends AbstractService {
    public function _registerSession($user)
    {
        $di = DI::getDefault();

        $di->getSession()->set(
            "auth",
            [
                "id" => $user->getUserId(),
                "email" => $user->getEmail(),
                "role" => $user->getRole()
            ]
        );
    }

    public function _registerSessionByData($data)
    {
        $di = DI::getDefault();

        $di->getSession()->set(
            "auth",
            [
                "id" => $data['userId'],
                "login" => $data['login'],
                "role" => $data['role']
            ]
        );
    }

    public function createSession($user){
        SupportClass::writeMessageInLogFile('Начало создания сессии для юзера '. $user->getEmail() != null ? $user->getEmail() : $user->phones->getPhone());
        $response = new Response();
        $lifetime = date('Y-m-d H:i:s',time() + 604800);
        $token = Accesstokens::GenerateToken($user->getUserId(), ($user->getEmail() != null ? $user->getEmail() : $user->phones->getPhone()),
            $user->getRole(), $lifetime);
        SupportClass::writeMessageInLogFile('ID юзера при этом - '. $user->getUserId());

        /*$accToken = new Accesstokens();

        $accToken->setUserid($user->getUserId());
        $accToken->setToken($token);
        $accToken->setLifetime();

        if ($accToken->save() == false) {
            SupportClass::writeMessageInLogFile('Не смог создать токен по указанной причине');
            $this->session->destroy();
            $errors = [];
            foreach ($accToken->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }
            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => $errors
                ]
            );
            return $response;
        }*/

        $this->_registerSession($user);

        $response->setJsonContent(
            [
                "status" => STATUS_OK,
                'token' => $token,
                'lifetime' => $lifetime
            ]
        );
        return $response;
    }
}
