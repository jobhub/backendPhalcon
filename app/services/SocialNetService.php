<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class SocialNetService extends AbstractService
{
    const ADDED_CODE_NUMBER = 26000;

    //const ERROR_UNABLE_CREATE_USER = 1 + self::ADDED_CODE_NUMBER;

    /**
     * Creating a new user
     *
     * @param array $userData
     * @return Users. If all ok, return Users object
     */
    public function registerUserByNet(array $userData)
    {
    }

    public function createUserSocial(array $userSocialData){

    }
}
