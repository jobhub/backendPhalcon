<?php

namespace App\Services;

use App\Models\FavoriteCategories;
use App\Models\FavoriteUsers;
use App\Models\FavouriteModel;
use App\Models\FavouriteProducts;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\FavoriteCompanies;
use App\Models\FavouriteServices;
use App\Models\Settings;

/**
 * business logic for users
 *
 * Class UsersService
 */
class FavouriteService extends AbstractService
{

    const TYPE_USER = 'user';
    const TYPE_COMPANY = 'company';
    const TYPE_SERVICE = 'service';
    const TYPE_CATEGORY = 'category';
    const TYPE_PRODUCT = 'product';

    const ADDED_CODE_NUMBER = 21000;

    /** Unable to create user */

    const ERROR_UNABLE_SUBSCRIBE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_UNSUBSCRIBE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_ACCOUNT_NOT_SUBSCRIBED = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_FAVOURITE_TYPE = 4 + self::ADDED_CODE_NUMBER;

    public function createNewObjectByType($type, $data = null)
    {
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
            case self::TYPE_CATEGORY:
                $fav = new FavoriteCategories();

                if (isset($data['radius']))
                    $fav->setRadius($data['radius']);

                break;
            case self::TYPE_PRODUCT:
                $fav = new FavouriteProducts();
                break;
            default:
                throw new ServiceException('Invalid type of forward', self::ERROR_INVALID_FAVOURITE_TYPE);
        }
        return $fav;
    }

    public function getModelByType($type)
    {
        switch ($type) {
            case self::TYPE_USER:
                $model = 'App\Models\FavoriteUsers';
                break;
            case self::TYPE_COMPANY:
                $model = 'App\Models\FavoriteCompanies';
                break;
            case self::TYPE_SERVICE:
                $model = 'App\Models\FavouriteServices';
                break;
            case self::TYPE_CATEGORY:
                $model = 'App\Models\FavoriteCategories';
                break;
            case self::TYPE_PRODUCT:
                $model = 'App\Models\FavouriteProducts';
                break;
            default:
                throw new ServiceException('Invalid type of signing', self::ERROR_INVALID_FAVOURITE_TYPE);
        }
        return $model;
    }

    public function subscribeTo($type, int $accountId, int $objectId, $data = null)
    {
        try {
            $fav = $this->createNewObjectByType($type,$data);
            $fav->setSubjectId($accountId);
            $fav->setObjectId($objectId);

            if (!$fav->create()) {
                $errors = SupportClass::getArrayWithErrors($fav);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable subscribe account to object',
                        self::ERROR_UNABLE_SUBSCRIBE, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable subscribe account to object',
                        self::ERROR_UNABLE_SUBSCRIBE);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getSigningTo($type, int $accountId, int $objectId)
    {

        $model = $this->getModelByType($type);

        $fav = FavouriteModel::findByIds($model, $accountId, $objectId);

        if (!$fav) {
            throw new ServiceException('Account not subscribed to object', self::ERROR_ACCOUNT_NOT_SUBSCRIBED);
        }
        return $fav;
    }

    public function unsubscribeFrom(FavouriteModel $fav)
    {
        if (!$fav->delete()) {
            $errors = SupportClass::getArrayWithErrors($fav);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable unsubscribe account from object',
                    self::ERROR_UNABLE_UNSUBSCRIBE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable unsubscribe account from object',
                    self::ERROR_UNABLE_UNSUBSCRIBE);
            }
        }
    }
}
