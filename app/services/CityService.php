<?php

namespace App\Services;

use App\Libs\ImageLoader;
use App\Models\Accounts;
use App\Models\Cities;
use App\Models\FavoriteUsers;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\FavoriteCompanies;
use App\Models\Settings;
use Phalcon\Http\Client\Exception;

use Phalcon\Http\Request\File as PhalconFile;


class CityService extends AbstractService
{
    const ADDED_CODE_NUMBER = 32000;

    /** Unable to create user */
    const ERROR_CITY_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;

    public function getCityById(int $cityId)
    {
        try {
            $city = Cities::findFirstByCityId($cityId);

            if (!$city || $city == null) {
                throw new ServiceException('City don\'t exists', self::ERROR_CITY_NOT_FOUND);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $city;
    }

}
