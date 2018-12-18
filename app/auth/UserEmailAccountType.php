<?php

namespace App\Auth;

use App\Constants\Services;
use Phalcon\Di;
use PhalconRest\Auth\Manager;

class UserEmailAccountType implements \PhalconRest\Auth\AccountType
{
    const NAME = "email";

    public function login($data)
    {
        /** @var \Phalcon\Security $security */
        $security = Di::getDefault()->get(Services::SECURITY);

        $username = $data[Manager::LOGIN_DATA_USERNAME];
        $password = $data[Manager::LOGIN_DATA_PASSWORD];

        /** @var \User $user */
        $user = \App\Models\User::findFirst([
            'conditions' => 'email = :email:',
            'bind' => ['email' => $username]
        ]);

        if(!$user){
            return null;
        }

        if(!$security->checkHash($password, $user->password)){
            return null;
        }

        return (string)$user->id;
    }

    public function authenticate($identity)
    {
        return \App\Models\User::existsById((int)$identity);
    }
}

