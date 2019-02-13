<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;
use App\Models\UsersSocial;

/**
 * business logic for users
 *
 * Class UsersService
 */
class SocialNetService extends AbstractService
{
    const ADDED_CODE_NUMBER = 26000;

    const ERROR_UNABLE_CREATE_USER_SOCIAL = 1 + self::ADDED_CODE_NUMBER;

    const ERROR_INFORMATION_FROM_NET_NOT_ENOUGH = 2 + self::ADDED_CODE_NUMBER;

    /**
     *  Регистрирует пользователя через соц сеть (по полученной информации)
     *
     * @param array $userData [phone, email, first_name, last_name, male, country, city,
     *                         network, identity, profile]
     * @return Users. If all ok, return Users object
     */
    public function registerUserByNet(array $userData)
    {
        if (isset($userFromULogin['phone'])) {
            $data['login'] = $userData['phone'];
        } elseif ($userData['email']) {
            $data['login'] = $userData['email'];
        } else {
            throw new ServiceException('Нужен email или телефон', self::ERROR_INFORMATION_FROM_NET_NOT_ENOUGH);
        }

        $resultUser = $this->userService->createUser($data);

        $account = $this->accountService->createAccount(['user_id' => $resultUser->getUserId()]);
    }

    public function createUserSocial(array $userSocialData, $userId){
        try {
            $userSocial = new UsersSocial();
            $userSocial->setUserId($userId);

            $this->fillUserSocial($userSocial, $userSocialData);

            if ($userSocial->create() == false) {
                SupportClass::getErrorsWithException($userSocial, self::ERROR_UNABLE_CREATE_USER_SOCIAL,
                    'Unable to add info for user from social net');
            }

            return $userSocial;
        }catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function fillUserSocial(UsersSocial $social, array $data){
        if(isset($data['network']))
            $social->setNetwork($data['network']);
        if(isset($data['identity']))
            $social->setIdentity($data['identity']);
        if(isset($data['profile']))
            $social->setProfile($data['profile']);
    }
}
