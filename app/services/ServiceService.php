<?php

namespace App\Services;

use App\Models\Services;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;
use App\Models\FavouriteServices;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\Settings;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ServiceService extends AbstractService {

    const ADDED_CODE_NUMBER = 9000;

    /** Unable to create user */
    const ERROR_SERVICE_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_SERVICE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_SERVICE = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_SERVICE = 4 + self::ADDED_CODE_NUMBER;

    /*const ERROR_UNABLE_SUBSCRIBE_USER_TO_SERVICE = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_SERVICE = 6 + self::ADDED_CODE_NUMBER;
    const ERROR_USER_NOT_SUBSCRIBED_TO_SERVICE = 7 + self::ADDED_CODE_NUMBER;*/

    public function createService(array $serviceData){
        $service = new Services();

        $this->fillService($service,$serviceData);

        if ($service->save() == false) {
            $errors = SupportClass::getArrayWithErrors($service);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable create service',
                    self::ERROR_UNABLE_CREATE_SERVICE,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable create service',
                    self::ERROR_UNABLE_CREATE_SERVICE);
            }
        }

        return $service;
    }

    public function changeService(Services $service, array $serviceData){
        $this->fillService($service,$serviceData);
        if ($service->update() == false) {
            $errors = SupportClass::getArrayWithErrors($service);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable change service',
                    self::ERROR_UNABLE_CHANGE_SERVICE,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable change service',
                    self::ERROR_UNABLE_CHANGE_SERVICE);
            }
        }
        return $service;
    }

    private function fillService(Services $service, array $data){
        if(!empty(trim($data['name'])))
            $service->setName($data['name']);
        if(!empty(trim($data['description'])))
            $service->setDescription($data['description']);
        if(!empty(trim($data['date_publication'])))
            $service->setDatePublication(date('Y-m-d H:m', strtotime($data['date_publication'])));
        if(!empty(trim($data['price_min'])))
            $service->setPriceMin($data['price_min']);
        if(!empty(trim($data['price_max'])))
            $service->setPriceMax($data['price_max']);
        if(!empty(trim($data['region_id'])))
            $service->setRegionId($data['region_id']);
        if(!empty(trim($data['longitude'])))
            $service->setLongitude($data['longitude']);
        if(!empty(trim($data['latitude'])))
            $service->setLatitude($data['latitude']);
        if(!empty(trim($data['number_of_display'])))
            $service->setNumberOfDisplay($data['number_of_display']);
        if(!empty(trim($data['rating'])))
            $service->setRating($data['rating']);
        if(!empty(trim($data['account_id'])))
            $service->setAccountId($data['account_id']);
    }

    public function getServiceById(int $serviceId){
        $service = Services::findFirstByServiceId($serviceId);

        if (!$service || $service == null) {
            throw new ServiceException('Service don\'t exists', self::ERROR_SERVICE_NOT_FOUND);
        }
        return $service;
    }

    public function deleteService(Services $service){
        if (!$service->delete()) {
            $errors = SupportClass::getArrayWithErrors($service);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete service',
                    self::ERROR_UNABLE_DELETE_SERVICE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete service',
                    self::ERROR_UNABLE_DELETE_SERVICE);
            }
        }
    }

    /*public function subscribeToService(int $accountId, int $serviceId){
        $fav = new FavouriteServices();
        $fav->setSubjectId($accountId);
        $fav->setServiceId($serviceId);

        if(!$fav->create()){
            SupportClass::getErrorsWithException($fav,self::ERROR_UNABLE_SUBSCRIBE_USER_TO_SERVICE,'Unable subscribe user to service');
        }
    }*/

    /*public function getSigningToService(int $accountId, int $serviceId){
        $fav = FavouriteServices::findFavouriteByIds($accountId,$serviceId);

        if (!$fav) {
            throw new ServiceException('User don\'t subscribed to service', self::ERROR_USER_NOT_SUBSCRIBED_TO_SERVICE);
        }
        return $fav;
    }

    public function unsubscribeFromService(FavouriteServices $favService){
        if(!$favService->delete()){
            SupportClass::getErrorsWithException($favService,
                self::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_SERVICE,'Unable unsubscribe user from service');
        }
    }*/
}
