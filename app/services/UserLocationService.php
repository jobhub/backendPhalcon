<?php

namespace App\Services;

use App\Models\UserLocation;
use Phalcon\DI\FactoryDefault as DI;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class UserLocationService extends AbstractService
{
    const ADDED_CODE_NUMBER = 15000;

    const ERROR_UNABLE_CREATE_USER_LOCATION = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_USER_LOCATION_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_USER_LOCATION = 3 + self::ADDED_CODE_NUMBER;

    /**
     * Создает объект с местоположением пользователя
     *
     * @param array $locationData
     * @param $userId
     * @return UserLocation. If all ok, return UserLocation object
     */
    public function createUserLocation(array $locationData, $userId)
    {
        $userLocation = new UserLocation();

        $userLocation->setUserId($userId);

        $marker = $this->markerService->createMarker($locationData['longitude'], $locationData['latitude']);
        $locationData['marker_id'] = $marker->getMarkerId();

        $this->fillUserLocation($userLocation, $locationData);

        if ($userLocation->create() == false) {
            $errors = SupportClass::getArrayWithErrors($userLocation);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to create user_location',
                    self::ERROR_UNABLE_CREATE_USER_LOCATION, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to create user_location',
                    self::ERROR_UNABLE_CREATE_USER_LOCATION);
            }
        }

        return $userLocation;
    }

    public function getUserLocationById(int $userId)
    {
        $userLocation = UserLocation::findFirstByUserId($userId);

        if (!$userLocation) {
            throw new ServiceException('News don\'t exists', self::ERROR_USER_LOCATION_NOT_FOUND);
        }
        return $userLocation;
    }

    public function getUserDataWithLocationById(int $userId)
    {
        return UserLocation::getUserinfo($userId);
    }

    public function fillUserLocation(UserLocation $userLocation, array $data)
    {
        if (!empty(trim($data['marker_id'])))
            $userLocation->setMarkerId($data['marker_id']);
        if (!empty(trim($data['last_time'])))
            $userLocation->setLastTime(date('Y-m-d H:i:sO', strtotime($data['last_time'])));
    }

    public function changeUserLocation(UserLocation $userLocation, array $data)
    {
        $this->markerService->changeMarker($userLocation->getMarkerId(), $data['longitude'], $data['latitude']);
        $this->fillUserLocation($userLocation, $data);

        if ($userLocation->update() == false) {
            $errors = SupportClass::getArrayWithErrors($userLocation);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to change user location',
                    self::ERROR_UNABLE_CHANGE_USER_LOCATION, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to change user location',
                    self::ERROR_UNABLE_CHANGE_USER_LOCATION);
            }
        }

        return $userLocation;
    }

    public function findUsers($data)
    {
        $longitudeHR = $data['diagonal']['longitude'];
        $latitudeHR = $data['diagonal']['latitude'];

        $diffLong = $data['diagonal']['longitude'] - $data['center']['longitude'];
        $longitudeLB = $data['center']['longitude'] - $diffLong;

        $diffLat = $data['diagonal']['latitude'] - $data['center']['latitude'];
        $latitudeLB = $data['center']['latitude'] - $diffLat;

        if (isset($data['age_min']))
            $data['age_min'] = intval($data['age_min']);
        if (isset($data['age_max']))
            $data['age_max'] = intval($data['age_max']);
        if (isset($data['male']))
            $data['male'] = intval($data['male']);

        if (isset($data['has_photo'])) {
            if (is_string($data['has_photo']) && $data['has_photo'] == "false")
                $data['has_photo'] = false;
            else
                $data['has_photo'] = boolval($data['has_photo']);
        }

        $results = UserLocation::findUsersByQueryWithFilters($data['query'],
            $longitudeHR, $latitudeHR, $longitudeLB, $latitudeLB, $data['age_min'],
            $data['age_max'], $data['male'], $data['has_photo'],$data['page'],$data['page_size']);

        return $results;
    }

    public function getAutocomplete($data)
    {
        $longitudeHR = $data['diagonal']['longitude'];
        $latitudeHR = $data['diagonal']['latitude'];

        $diffLong = $data['diagonal']['longitude'] - $data['center']['longitude'];
        $longitudeLB = $data['center']['longitude'] - $diffLong;

        $diffLat = $data['diagonal']['latitude'] - $data['center']['latitude'];
        $latitudeLB = $data['center']['latitude'] - $diffLat;

        $results = UserLocation::getAutoComplete($data['query'],
            $longitudeHR, $latitudeHR, $longitudeLB, $latitudeLB,
            $data['page'],$data['page_size']);

        return $results;
    }
}
