<?php

namespace App\Services;

//Models
use App\Models\Phones;
use App\Models\PhonesPoints;

use App\Libs\SupportClass;

/**
 * business and other logic for authentication. Maybe just creation simple objects.
 *
 * Class UsersService
 */
class PhoneService extends AbstractService {

    const ADDED_CODE_NUMBER = 12000;

    const ERROR_UNABLE_CREATE_PHONE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_ADD_PHONE_TO_POINT = 2 + + self::ADDED_CODE_NUMBER;
    /**
     * Create phone if it don't exists.
     *
     * @param $phone
     * @return Phones. If all ok - return id of new (or old) phone. Else return array of the errors.
     */
    public function createPhone($phone)
    {
        $phoneObject = new Phones();
        $phone = Phones::formatPhone($phone);
        $phoneObject->setPhone($phone);

        if ($phoneObject->save() == false) {
            $errors = SupportClass::getArrayWithErrors($phoneObject);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable add phone',
                    self::ERROR_UNABLE_CREATE_PHONE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable add phone',
                    self::ERROR_UNABLE_CREATE_PHONE);
            }
        }
        return $phoneObject;
    }

    public function addPhoneToPoint(string $phone, int $pointId)
    {
        $phoneObject = $this->createPhone($phone);

        $phonePoint = new PhonesPoints();
        $phonePoint->setPhoneId($phoneObject->getPhoneId());
        $phonePoint->setPointId($pointId);

        if (!$phonePoint->create()) {
            $errors = SupportClass::getArrayWithErrors($phonePoint);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable add phone to point',
                    self::ERROR_UNABLE_ADD_PHONE_TO_POINT,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable add phone to point',
                    self::ERROR_UNABLE_ADD_PHONE_TO_POINT);
            }
        }

        return $phonePoint;
    }
}
