<?php

namespace App\Services;

use App\Models\FavoriteUsers;
use App\Models\FavouriteModel;
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
class FavouriteService extends AbstractService {

    const TYPE_USER = 'user';
    const TYPE_COMPANY = 'company';
    const TYPE_SERVICE = 'service';

    const ADDED_CODE_NUMBER = 21000;

    /** Unable to create user */

    const ERROR_UNABLE_SUBSCRIBE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_UNSUBSCRIBE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_ACCOUNT_NOT_SUBSCRIBED = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_FAVOURITE_TYPE = 4 + self::ADDED_CODE_NUMBER;

    public function subscribeTo($type, int $accountId, int $objectId){
        switch ($type) {
            case self::TYPE_USER:
                $fav = new FavoriteUsers();
                break;
            case self::TYPE_COMPANY:
                $fav = new FavoriteCompanies();
                break;
            case self::TYPE_SERVICE:
                $fav = new FavouriteServices();
                break;
            default:
                throw new ServiceException('Invalid type of forward', self::ERROR_INVALID_FAVOURITE_TYPE);
        }
        $fav->setSubjectId($accountId);
        $fav->setObjectId($objectId);

        if(!$fav->create()){
            $errors = SupportClass::getArrayWithErrors($fav);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable subscribe account to object',
                    self::ERROR_UNABLE_SUBSCRIBE,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable subscribe account to object',
                    self::ERROR_UNABLE_SUBSCRIBE);
            }
        }
    }

    public function getSigningTo($type, int $accountId, int $objectId){

        switch ($type) {
            case self::TYPE_USER:
                $model = 'App\Models\FavoriteUsers';
                break;
            case self::TYPE_COMPANY:
                $model = 'App\Models\FavoriteCompanies';
                break;
            case self::TYPE_SERVICE:
                $model = 'App\Models\FavoriteServices';
                break;
            default:
                throw new ServiceException('Invalid type of signing', self::ERROR_INVALID_FAVOURITE_TYPE);
        }

        $fav = FavouriteModel::findByIds($model,$accountId,$objectId);

        if (!$fav) {
            throw new ServiceException('Account don\'t subscribe to object', self::ERROR_ACCOUNT_NOT_SUBSCRIBED);
        }
        return $fav;
    }

    public function unsubscribeFrom(FavouriteModel $fav){
        if(!$fav->delete()){
            $errors = SupportClass::getArrayWithErrors($fav);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable unsubscribe account from object',
                    self::ERROR_UNABLE_UNSUBSCRIBE,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable unsubscribe account from object',
                    self::ERROR_UNABLE_UNSUBSCRIBE);
            }
        }
    }

    public function getFavourites($type, int $accountId){

        switch ($type) {
            case self::TYPE_USER:
                $model = 'App\Models\FavoriteUsers';
                break;
            case self::TYPE_COMPANY:
                $model = 'App\Models\FavoriteCompanies';
                break;
            case self::TYPE_SERVICE:
                $model = 'App\Models\FavoriteServices';
                break;
            default:
                throw new ServiceException('Invalid type of signing', self::ERROR_INVALID_FAVOURITE_TYPE);
        }

        /*$fav = FavouriteModel::findByIds($model,$accountId,$objectId);

        if (!$fav) {
            throw new ServiceException('Account don\'t subscribe to object', self::ERROR_ACCOUNT_NOT_SUBSCRIBED);
        }
        return $fav;*/
    }
}
