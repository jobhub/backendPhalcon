<?php

namespace App\Services;

use App\Models\Services;
use App\Models\TagsEvents;
use App\Models\TagsModel;
use App\Models\TagsProducts;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\TagsServices;
use App\Models\Tags;

/**
 * business logic for users
 *
 * Class UsersService
 */
class TagService extends AbstractService {

    const TYPE_SERVICE = 'service';
    const TYPE_PRODUCT = 'product';
    const TYPE_EVENT = 'event';

    const ADDED_CODE_NUMBER = 10000;

    const ERROR_TAG_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_TAG = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_TAG = 3 + self::ADDED_CODE_NUMBER;


    const ERROR_INVALID_INVITE_TYPE = 4 + self::ADDED_CODE_NUMBER;

    public function createNewObjectByType($type)
    {
        switch ($type) {
            case self::TYPE_SERVICE:
                $tag = new TagsServices();
                break;
            case self::TYPE_PRODUCT:
                $tag = new TagsProducts();
                break;
            case self::TYPE_EVENT:
                $tag = new TagsEvents();
                break;
            default:
                throw new ServiceException('Invalid type of tag relation', self::ERROR_INVALID_INVITE_TYPE);
        }
        return $tag;
    }

    public function getModelByType($type)
    {
        switch ($type) {
            case self::TYPE_SERVICE:
                $tagRelation = 'App\\Models\\TagsServices';
                break;
            case self::TYPE_PRODUCT:
                $tagRelation = 'App\\Models\\TagsProducts';
                break;
            case self::TYPE_EVENT:
                $tagRelation = 'App\\Models\\TagsEvents';
                break;
            default:
                throw new ServiceException('Invalid type of invite', self::ERROR_INVALID_INVITE_TYPE);
        }
        return $tagRelation;
    }

    public function addTagToObject(string $tag, int $objectId, $type){

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

        $tagRelation = self::createNewObjectByType($type);
        $tagRelation->setObjectId($objectId);
        $tagRelation->setTagId($tagObject->getTagId());

        if (!$tagRelation->save()) {
            $errors = SupportClass::getArrayWithErrors($tagRelation);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable add tag to object',
                    self::ERROR_UNABLE_CREATE_TAG,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable add tag to object',
                    self::ERROR_UNABLE_CREATE_TAG);
            }
        }

        return $tagRelation;
    }

    public function addTagToService(string $tag, int $serviceId){
        $this->addTagToObject($tag,$serviceId,self::TYPE_SERVICE);
    }

    public function getTagForService($tagId, int $serviceId){
        $this->getTagForObject($tagId,$serviceId,self::TYPE_SERVICE);
    }

    public function deleteTagFromService(TagsServices $tagRelation){
        $this->deleteTagFromObject($tagRelation);
    }

    public function getTagForObject($tagId, int $objectId, $type){

        $model = self::getModelByType($type);
        $tag = $model::findByIds($objectId,$tagId);

        if (!$tag || $tag == null) {
            throw new ServiceException('Tag don\'t exists', self::ERROR_TAG_NOT_FOUND);
        }

        return $tag;
    }

    public function getTagsForObject(int $objectId, $type){

        $model = self::getModelByType($type);
        $modelsManager = DI::getDefault()->get('modelsManager');

        $result = $modelsManager->createBuilder()
            ->from(["t" => "App\Models\Tags"])
            ->join($model, 't.tag_id = st.tag_id', 'st')
            ->where('st.object_id = :objectId:', ['objectId' => $objectId])
            ->getQuery()
            ->execute();

        return $result;
    }

    public function deleteTagFromObject(TagsModel $tagRelation){
        if (!$tagRelation->delete()) {
            $errors = SupportClass::getArrayWithErrors($tagRelation);
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
