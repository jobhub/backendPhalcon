<?php

namespace App\Services;

use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\Settings;

/**
 * business logic for users
 *
 * Class UsersService
 */
class UserInfoService extends AbstractService {

    const ADDED_CODE_NUMBER = 4000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_USER_INFO = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_SETTINGS = 2 + self::ADDED_CODE_NUMBER;

    public function CreateUserInfo(array $userInfoData){
        $userInfo = new Userinfo();
        $userInfo->setUserId($userInfoData['userId']);

        $this->fillUserInfo($userInfo,$userInfoData);

        if ($userInfo->save() == false) {
            $errors = SupportClass::getArrayWithErrors($userInfo);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable create info for user',
                    self::ERROR_UNABLE_CREATE_USER_INFO,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable create info for user',
                    self::ERROR_UNABLE_CREATE_USER_INFO);
            }
        }
        return $userInfo;
    }

    public function fillUserInfo(Userinfo $userInfo, array $data){
        if(!empty(trim($data['firstname'])))
            $userInfo->setFirstname($data['firstname']);
        if(!empty(trim($data['lastname'])))
            $userInfo->setLastname($data['lastname']);
        if(!empty(trim($data['patronymic'])))
            $userInfo->setPatronymic($data['patronymic']);
        if(!empty(trim($data['male'])))
            $userInfo->setMale($data['male']);
        if(!empty(trim($data['address'])))
            $userInfo->setAddress($data['address']);
        if(!empty(trim($data['birthday'])))
            $userInfo->setBirthday(date('Y-m-d H:m', strtotime($data['birthday'])));
        if(!empty(trim($data['about'])))
            $userInfo->setAbout($data['about']);
        if(!empty(trim($data['status'])))
            $userInfo->setStatus($data['status']);
        if(!empty(trim($data['email'])))
            $userInfo->setEmail($data['email']);
    }

    public function CreateSettings(int $userId){
        $setting = new Settings();
        $setting->setUserId($userId);

        if ($setting->save() == false) {
            $errors = SupportClass::getArrayWithErrors($setting);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable create settings',
                    self::ERROR_UNABLE_CREATE_SETTINGS,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable create settings',
                    self::ERROR_UNABLE_CREATE_SETTINGS);
            }
        }

        return $setting;
    }
}
