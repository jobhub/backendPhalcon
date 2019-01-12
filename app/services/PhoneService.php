<?php

namespace App\Services;

//Models
use App\Models\Phones;
use App\Models\PhonesCompanies;
use App\Models\PhonesPoints;

use App\Libs\SupportClass;
use App\Models\PhonesUsers;

/**
 * business and other logic for authentication. Maybe just creation simple objects.
 *
 * Class UsersService
 */
class PhoneService extends AbstractService {

    const ADDED_CODE_NUMBER = 12000;

    const ERROR_UNABLE_CREATE_PHONE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_ADD_PHONE_TO_POINT = 2 + + self::ADDED_CODE_NUMBER;
    const ERROR_PHONE_POINT_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_PHONE_COMPANY_NOT_FOUND = 4 + + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_ADD_PHONE_TO_COMPANY = 5 + + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_PHONE_FROM_POINT = 6 + + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_PHONE_FROM_COMPANY = 7 + + self::ADDED_CODE_NUMBER;
    const ERROR_PHONE_NOT_FOUND = 8 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_PHONE = 9 + self::ADDED_CODE_NUMBER;
    const ERROR_PHONE_USER_NOT_FOUND = 10 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_ADD_PHONE_TO_USER = 11 + + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_PHONE_FROM_USER = 12 + + self::ADDED_CODE_NUMBER;
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

    public function getPhonePointById(int $phoneId, int $pointId){
        $phonePoint = PhonesPoints::findByIds($pointId,$phoneId);

        if(!$phonePoint){
            throw new ServiceException('Phone is not connected with a trade point', self::ERROR_PHONE_POINT_NOT_FOUND);
        }
        return $phonePoint;
    }

    public function getPhoneById($phoneId){
        $phone = Phones::findFirstByPhoneId($phoneId);

        if(!$phone){
            throw new ServiceException('Phone don\'t exists', self::ERROR_PHONE_NOT_FOUND);
        }
        return $phone;
    }

    public function getPhoneCompanyById(int $phoneId, int $companyId){
        $phoneCompany = PhonesCompanies::findByIds($companyId,$phoneId);

        if(!$phoneCompany){
            throw new ServiceException('Phone is not connected with a company', self::ERROR_PHONE_COMPANY_NOT_FOUND);
        }
        return $phoneCompany;
    }

    public function addPhoneToCompany(string $phone, int $companyId)
    {
        $phoneObject = $this->createPhone($phone);

        $phoneCompany = new PhonesCompanies();
        $phoneCompany->setPhoneId($phoneObject->getPhoneId());
        $phoneCompany->setCompanyId($companyId);

        if (!$phoneCompany->create()) {
            $errors = SupportClass::getArrayWithErrors($phoneCompany);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable add phone to company',
                    self::ERROR_UNABLE_ADD_PHONE_TO_COMPANY,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable add phone to company',
                    self::ERROR_UNABLE_ADD_PHONE_TO_COMPANY);
            }
        }

        return $phoneCompany;
    }

    public function deletePhonePoint(PhonesPoints $phonePoint){

        $phone = $this->getPhoneById($phonePoint->getPhoneId());

        if (!$phonePoint->delete()) {
            $errors = SupportClass::getArrayWithErrors($phonePoint);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete phone from point',
                    self::ERROR_UNABLE_DELETE_PHONE_FROM_POINT, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete phone from point',
                    self::ERROR_UNABLE_DELETE_PHONE_FROM_POINT);
            }
        }

        if ($phone->countOfReferences() == 0) {
            $this->deletePhone($phone);
        }
    }

    public function deletePhoneCompany(PhonesCompanies $phoneCompany){
        $phone = $this->getPhoneById($phoneCompany->getPhoneId());

        if (!$phoneCompany->delete()) {
            $errors = SupportClass::getArrayWithErrors($phoneCompany);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete phone from company',
                    self::ERROR_UNABLE_DELETE_PHONE_FROM_COMPANY, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete phone from company',
                    self::ERROR_UNABLE_DELETE_PHONE_FROM_COMPANY);
            }
        }

        if ($phone->countOfReferences() == 0) {
            $this->deletePhone($phone);
        }
    }

    public function deletePhone(Phones $phone){
        if(!$phone->delete()){
            $errors = SupportClass::getArrayWithErrors($phone);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete phone',
                    self::ERROR_UNABLE_DELETE_PHONE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete phone',
                    self::ERROR_UNABLE_DELETE_PHONE);
            }
        }
    }

    public function getPhoneUserById(int $phoneId, int $userId){
        $phoneUser = PhonesUsers::findByIds($userId,$phoneId);

        if(!$phoneUser){
            throw new ServiceException('Phone is not connected with a user', self::ERROR_PHONE_USER_NOT_FOUND);
        }
        return $phoneUser;
    }

    public function addPhoneToUser(string $phone, int $userId)
    {
        $phoneObject = $this->createPhone($phone);

        $phoneUser = new PhonesUsers();
        $phoneUser->setPhoneId($phoneObject->getPhoneId());
        $phoneUser->setUserId($userId);

        if (!$phoneUser->create()) {
            $errors = SupportClass::getArrayWithErrors($phoneUser);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable add phone to user',
                    self::ERROR_UNABLE_ADD_PHONE_TO_USER,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable add phone to user',
                    self::ERROR_UNABLE_ADD_PHONE_TO_USER);
            }
        }

        return $phoneUser;
    }

    public function deletePhoneUser(PhonesUsers $phoneUsers){

        $phone = $this->getPhoneById($phoneUsers->getPhoneId());

        if (!$phoneUsers->delete()) {
            $errors = SupportClass::getArrayWithErrors($phoneUsers);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete phone from user',
                    self::ERROR_UNABLE_DELETE_PHONE_FROM_USER, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete phone from user',
                    self::ERROR_UNABLE_DELETE_PHONE_FROM_USER);
            }
        }

        if ($phone->countOfReferences() == 0) {
            $this->deletePhone($phone);
        }
    }
}
