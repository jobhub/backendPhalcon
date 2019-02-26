<?php

namespace App\Services;

use App\Models\FavoriteUsers;
use App\Models\FavouriteModel;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Markers;

class MarkerService extends AbstractService {

    const ADDED_CODE_NUMBER = 22000;

    const ERROR_UNABLE_CREATE_MARKER = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_MARKER = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_MARKER_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;

    public function createMarker($longitude, $latitude)
    {
        $marker = new Markers();
        $marker->setLongitude($longitude);
        $marker->setLatitude($latitude);

        if ($marker->create() == false) {
            SupportClass::getErrorsWithException($marker,self::ERROR_UNABLE_CREATE_MARKER,'Unable create marker');
        }

        return $marker;
    }

    public function changeMarker($markerId, $longitude, $latitude)
    {
        $marker = $this->getMarkerById($markerId);
        $marker->setLongitude($longitude);
        $marker->setLatitude($latitude);

        if ($marker->update() == false) {
            SupportClass::getErrorsWithException($marker,self::ERROR_UNABLE_CREATE_MARKER,'Unable change marker');
        }

        return $marker;
    }

    public function getMarkerById(int $markerId)
    {
        $marker = Markers::findFirstByMarkerId($markerId);

        if (!$marker) {
            throw new ServiceException('Marker not exists', self::ERROR_MARKER_NOT_FOUND);
        }
        return $marker;
    }
}
