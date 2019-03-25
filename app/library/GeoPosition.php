<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 25.03.2019
 * Time: 10:12
 */

namespace App\Libs;


class GeoPosition
{
    const MinLatitude = -85.05112878;
    const MaxLatitude = 85.05112878;
    const MinLongitude = -180;
    const MaxLongitude = 180;

    public static function checkLongitude($longitude)
    {
        $longitude = filter_var($longitude,FILTER_VALIDATE_FLOAT);

        if($longitude==null || !SupportClass::checkDouble($longitude))
            return false;
        if($longitude>=-180 && $longitude <= 180)
            return true;

        return false;
    }

    public static function checkLatitude($latitude)
    {
        $latitude = filter_var($latitude,FILTER_VALIDATE_FLOAT);

        if($latitude==null || !SupportClass::checkDouble($latitude))
            return false;
        if($latitude>=-85.05112878 && $latitude <= 85.05112878)
            return true;

        return false;
    }
}