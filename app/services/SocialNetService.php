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
    const ADDED_CODE_NUMBER = 30000;

    const ERROR_UNABLE_CREATE_USER_SOCIAL = 1 + self::ADDED_CODE_NUMBER;

    const ERROR_INFORMATION_FROM_NET_NOT_ENOUGH = 2 + self::ADDED_CODE_NUMBER;

    const ERROR_UNABLE_AUTHENTICATE_IN_NET = 3 + self::ADDED_CODE_NUMBER;

    /**
     *  Регистрирует пользователя через соц сеть (по полученной информации)
     *
     * @param array $userData [phone, email, first_name, last_name, male, country, city,
     *                         network, identity, profile, status, about, uri_to_photo, photo_name]
     * @return Users. If all ok, return Users object
     */
    public function registerUserByNet(array $userData)
    {
        if (isset($userData['phone'])) {
            $data['login'] = $userData['phone'];
        } elseif ($userData['email']) {
            $data['login'] = $userData['email'];
        } /*else {
            throw new ServiceException('Нужен email или телефон', self::ERROR_INFORMATION_FROM_NET_NOT_ENOUGH);
        }*/

        $data['is_social'] = true;
        $resultUser = $this->userService->createUser($data);

        $account = $this->accountService->createAccount(['user_id' => $resultUser->getUserId()]);

        $data_userinfo['user_id'] = $resultUser->getUserId();
        $data_userinfo['first_name'] = $userData['first_name'];
        $data_userinfo['last_name'] = $userData['last_name'];
        $data_userinfo['male'] = ($userData['sex'] - 1) >= 0 ? $userData['sex'] - 1 : 1;
        $data_userinfo['birthday'] = $userData['birthday'];
        $data_userinfo['status'] = $userData['status'];
        $data_userinfo['about'] = $userData['about'];

        if (isset($userData['country']) && isset($userData['city']))
            $data_userinfo['address'] = ($userData['country'] . ' ' . $userData['city']);

        $data_userinfo['city_id'] = $userData['city_id'];

        $data_userinfo['nickname'] = 'nickname_'.$resultUser->getUserId();

        while($this->userInfoService->checkNicknameExists($data_userinfo['nickname'])){
            $data_userinfo['nickname'].=rand(0,9);
        }

        $userInfo = $this->userInfoService->createUserInfo($data_userinfo);

        $userInfo = $this->userInfoService->getUserInfobyId($userInfo->getUserId());

        $this->userInfoService->createSettings($resultUser->getUserId());
        $this->userService->setNewRoleForUser($resultUser, ROLE_USER);

        SupportClass::writeMessageInLogFile('До изменения фотографии пользователя');

        $this->userInfoService->savePhotoForUserByURL($resultUser,$userInfo,$userData['uri_to_photo'], $userData['photo_name']);

        SupportClass::writeMessageInLogFile('Изменил фотографию пользователя');

        $userSocialData['network'] = $userData['network'];
        $userSocialData['profile'] = $userData['profile'];
        $userSocialData['identity'] = $userData['identity'];

        $this->createUserSocial($userSocialData,$resultUser->getUserId());

        $strUser = var_export($resultUser,true);

        SupportClass::writeMessageInLogFile('вовращает зареганного юзера '.$strUser);
        return $resultUser;
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
