<?php

namespace App\Services;

use App\Models\Services;
use App\Models\Users;
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
class TagService extends AbstractService {

    const ADDED_CODE_NUMBER = 10000;

    const ERROR_TAG_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_TAG = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_TAG = 3 + self::ADDED_CODE_NUMBER;

    public function addTagToService(string $tag, int $serviceId){

        $tagObject = new Tags();
        $tagObject->setTag($tag);

        if (!$tagObject->save()) {
            $errors = SupportClass::getArrayWithErrors($tagObject);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable create tag',
                    self::ERROR_UNABLE_CREATE_TAG,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable create tag',
                    self::ERROR_UNABLE_CREATE_TAG);
            }
        }

        $serviceTag = new ServicesTags();
        $serviceTag->setServiceId($serviceId);
        $serviceTag->setTagId($tagObject->getTagId());

        if (!$serviceTag->save()) {
            $errors = SupportClass::getArrayWithErrors($serviceTag);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable create tag for service',
                    self::ERROR_UNABLE_CREATE_TAG,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable create tag for service',
                    self::ERROR_UNABLE_CREATE_TAG);
            }
        }

        return $serviceTag;
    }

    public function getTagForService(int $tagId, int $serviceId){
        $tag = ServicesTags::findByIds($serviceId,$tagId);

        if (!$tag || $tag == null) {
            throw new ServiceException('Tag don\'t exists', self::ERROR_TAG_NOT_FOUND);
        }

        return $tag;
    }

    public function deleteTagFromService(ServicesTags $serviceTag){
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
    }
}
