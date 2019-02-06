<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\FavoriteUsers;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\FavoriteCompanies;
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
    const ERROR_USER_INFO_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_USER_INFO = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_SUBSCRIBE_USER_TO_COMPANY = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_COMPANY = 6 + self::ADDED_CODE_NUMBER;
    const ERROR_USER_NOT_SUBSCRIBED_TO_COMPANY = 7 + self::ADDED_CODE_NUMBER;

    const ERROR_UNABLE_SUBSCRIBE_USER_TO_USER = 8 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_USER = 9 + self::ADDED_CODE_NUMBER;
    const ERROR_USER_NOT_SUBSCRIBED_TO_USER = 10 + self::ADDED_CODE_NUMBER;

    public function createUserInfo(array $userInfoData){
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

    public function changeUserInfo(Userinfo $userInfo, array $userInfoData){
        $this->fillUserInfo($userInfo,$userInfoData);
        if ($userInfo->update() == false) {
            $errors = SupportClass::getArrayWithErrors($userInfo);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable change info for user',
                    self::ERROR_UNABLE_CHANGE_USER_INFO,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable change info for user',
                    self::ERROR_UNABLE_CHANGE_USER_INFO);
            }
        }
        return $userInfo;
    }

    private function fillUserInfo(Userinfo $userInfo, array $data){
        if(!empty(trim($data['first_name'])))
            $userInfo->setFirstName($data['first_name']);
        if(!empty(trim($data['last_name'])))
            $userInfo->setLastName($data['last_name']);
        if(isset($data['patronymic']))
            $userInfo->setPatronymic($data['patronymic']);
        if(isset($data['male']))
            $userInfo->setMale($data['male']);
        if(isset($data['address']))
            $userInfo->setAddress($data['address']);
        if(!empty(trim($data['birthday'])))
            $userInfo->setBirthday(date('Y-m-d H:i:sO', strtotime($data['birthday'])));
        if(isset($data['about']))
            $userInfo->setAbout($data['about']);
        if(isset($data['status']))
            $userInfo->setStatus($data['status']);
        if(isset($data['email']))
            $userInfo->setEmail($data['email']);
        if(isset($data['path_to_photo']))
            $userInfo->setPathToPhoto($data['path_to_photo']);
    }

    public function CreateSettings(int $userId){
        $setting = new Settings();
        $setting->setUserId($userId);

        if ($setting->create() == false) {
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

    public function getUserInfoById(int $userId){
        $user = Userinfo::findFirstByUserId($userId);

        if (!$user || $user == null) {
            throw new ServiceException('User don\'t exists', self::ERROR_USER_INFO_NOT_FOUND);
        }
        return $user;
    }

    public function getHandledUserInfoById(int $userId, Accounts $accountReceiver = null){
        $userInfo = Userinfo::findUserInfoById($userId);

        if (!$userInfo || $userInfo == null) {
            throw new ServiceException('User don\'t exists', self::ERROR_USER_INFO_NOT_FOUND);
        }

        return Userinfo::handleUserInfo($userInfo,$accountReceiver);
    }

    public function subscribeToCompany(int $userId, int $companyId){
        $fav = new FavoriteCompanies();
        $fav->setSubjectId($userId);
        $fav->setObjectId($companyId);

        if(!$fav->create()){
            $errors = SupportClass::getArrayWithErrors($fav);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable subscribe user to company',
                    self::ERROR_UNABLE_SUBSCRIBE_USER_TO_COMPANY,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable subscribe user to company',
                    self::ERROR_UNABLE_SUBSCRIBE_USER_TO_COMPANY);
            }
        }
    }

    public function getSigningToCompany(int $userId, int $companyId){
        $fav = FavoriteCompanies::findByIds('App\Models\FavoriteCompanies',$userId,$companyId);

        if (!$fav) {
            throw new ServiceException('User don\'t subscribe to company', self::ERROR_USER_NOT_SUBSCRIBED_TO_COMPANY);
        }
        return $fav;
    }

    public function unsubscribeFromCompany(FavoriteCompanies $favComp){
        if(!$favComp->delete()){
            $errors = SupportClass::getArrayWithErrors($favComp);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable unsubscribe user from company',
                    self::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_COMPANY,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable unsubscribe user from company',
                    self::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_COMPANY);
            }
        }
    }

    public function subscribeToUser(int $userId, int $userIdObject){
        $fav = new FavoriteUsers();
        $fav->setSubjectId($userId);
        $fav->setObjectId($userIdObject);

        if(!$fav->create()){
            SupportClass::getErrorsWithException($fav,self::ERROR_UNABLE_SUBSCRIBE_USER_TO_USER,'Unable subscribe user to user');
        }
    }

    public function getSigningToUser(int $userId, int $userIdObject){
        $fav = FavoriteUsers::findByIds('App\Models\FavoriteUsers',$userIdObject,$userId);

        if (!$fav) {
            throw new ServiceException('User don\'t subscribed to user', self::ERROR_USER_NOT_SUBSCRIBED_TO_USER);
        }
        return $fav;
    }

    public function unsubscribeFromUser(FavoriteUsers $favUser){
        if(!$favUser->delete()){
            SupportClass::getErrorsWithException($favUser,
                self::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_USER,'Unable unsubscribe user from user');
        }
    }
}
