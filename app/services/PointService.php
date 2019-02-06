<?php

namespace App\Services;

use App\Models\Markers;
use App\Models\Services;
use App\Models\TradePoints;
use App\Models\ServicesPoints;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\ServicesTags;
use App\Models\Tags;

/**
 * business logic for users
 *
 * Class UsersService
 */
class PointService extends AbstractService
{

    const ADDED_CODE_NUMBER = 11000;

    const ERROR_POINT_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_POINT = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_POINT = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_ADD_POINT_TO_SERVICE = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_POINT_FROM_SERVICE = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_POINT = 6 + self::ADDED_CODE_NUMBER;

    public function addPointToService(int $pointId, int $serviceId)
    {
        $servicePoint = new ServicesPoints();
        $servicePoint->setServiceId($serviceId);
        $servicePoint->setPointId($pointId);

        if (!$servicePoint->create()) {
            $errors = SupportClass::getArrayWithErrors($servicePoint);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable add point to service',
                    self::ERROR_UNABLE_ADD_POINT_TO_SERVICE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable add point to service',
                    self::ERROR_UNABLE_ADD_POINT_TO_SERVICE);
            }
        }

        return $servicePoint;
    }

    public function deletePointFromService(int $pointId, int $serviceId)
    {
        $servicePoint = ServicesPoints::findByIds($serviceId, $pointId);

        if (!$servicePoint->delete()) {
            $errors = SupportClass::getArrayWithErrors($servicePoint);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete point from service',
                    self::ERROR_UNABLE_DELETE_POINT_FROM_SERVICE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete point from service',
                    self::ERROR_UNABLE_DELETE_POINT_FROM_SERVICE);
            }
        }

        return $servicePoint;
    }

    public function getPointById(int $pointId)
    {
        $point = TradePoints::findFirstByPointId($pointId);
        if (!$point) {
            throw new ServiceException('Trade point don\'t exists', self::ERROR_POINT_NOT_FOUND);
        }
        return $point;
    }

    public function createPoint($data)
    {
        $point = new TradePoints();

        $marker = $this->markerService->createMarker($data['longitude'],$data['latitude']);
        $data['marker_id'] = $marker->getMarkerId();
        $this->fillPoint($point, $data);

        if ($point->save() == false) {
            $errors = SupportClass::getArrayWithErrors($point);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable create trade point',
                    self::ERROR_UNABLE_CREATE_POINT, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable create trade point',
                    self::ERROR_UNABLE_CREATE_POINT);
            }
        }

        return $point;
    }

    public function changePoint(TradePoints $point, $data)
    {
        $this->fillPoint($point, $data);
        if ($point->update() == false) {
            $errors = SupportClass::getArrayWithErrors($point);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable change trade point',
                    self::ERROR_UNABLE_CHANGE_POINT, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable change trade point',
                    self::ERROR_UNABLE_CHANGE_POINT);
            }
        }

        return $point;
    }

    private function fillPoint(TradePoints $point, array $data)
    {
        if (!empty(trim($data['name'])))
            $point->setName($data['name']);

        if (!empty(trim($data['fax'])))
            $point->setFax($data['fax']);
        if (!empty(trim($data['time'])))
            $point->setTime($data['time']);
        if (!empty(trim($data['email'])))
            $point->setEmail($data['email']);
        if (!empty(trim($data['user_manager'])))
            $point->setUserManager($data['user_manager']);
        if (!empty(trim($data['website'])))
            $point->setWebSite($data['website']);
        if (!empty(trim($data['address'])))
            $point->setWebSite($data['address']);
        if (!empty(trim($data['position_variable'])))
            $point->setPositionVariable($data['position_variable']);
        if (!empty(trim($data['account_id'])))
            $point->setAccountId($data['account_id']);

        /*if (!empty(trim($data['longitude'])))
            $point->setLongitude($data['longitude']);
        if (!empty(trim($data['latitude'])))
            $point->setLatitude($data['latitude']);*/
        if(!empty(trim($data['marker_id'])))
            $point->setMarkerId($data['marker_id']);
    }

    public function deletePoint(TradePoints $point)
    {
        if (!$point->delete()) {
            $errors = SupportClass::getArrayWithErrors($point);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete trade point',
                    self::ERROR_UNABLE_DELETE_POINT, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete trade point',
                    self::ERROR_UNABLE_DELETE_POINT);
            }
        }
    }

    private function clipDataForCreation($data)
    {
        $new_data['name'] = $data['name'];
        $new_data['longitude'] = $data['longitude'];
        $new_data['latitude'] = $data['latitude'];
        $new_data['fax'] = $data['fax'];
        $new_data['time'] = $data['time'];
        $new_data['email'] = $data['email'];
        $new_data['user_manager'] = $data['user_manager'];
        $new_data['website'] = $data['website'];
        $new_data['address'] = $data['address'];
        $new_data['position_variable'] = $data['position_variable'];

        return $new_data;
    }

    /*public function deleteTagFromService(ServicesTags $serviceTag){
        if (!$serviceTag->delete()) {
            $errors = SupportClass::getArrayWithErrors($serviceTag);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete service',
                    self::ERROR_UNABLE_DELETE_TAG, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete service',
                    self::ERROR_UNABLE_DELETE_TAG);
            }
        }
    }*/
}
