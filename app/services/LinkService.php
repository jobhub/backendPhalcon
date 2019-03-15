<?php

namespace App\Services;

use App\Libs\SupportClass;

use App\Models\CompaniesProductCategories;
use App\Models\FavoriteCategories;
use App\Models\Categories;
use App\Models\CompaniesCategories;
use App\Models\UsersCategories;

/**
 * business logic for users
 *
 * Class UsersService
 */
class LinkService extends AbstractService
{
    const TYPE_LINK_COMPANY_CATEGORY_SERVICE = 'company-category-service';
    const TYPE_LINK_USER_CATEGORY_SERVICE = 'user-category-service';

    const ADDED_CODE_NUMBER = 37000;

    const ERROR_UNABlE_LINK_CATEGORY_WITH_COMPANY = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABlE_LINK_CATEGORY_WITH_USER = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABlE_LINK_PRODUCT_CATEGORY_WITH_COMPANY = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_LINK_TYPE = 6 + self::ADDED_CODE_NUMBER;

    /*public function getModelByType($link_type)
    {
        switch ($link_type) {
            case self::TYPE_SERVICE:
                $model = 'Categories';
                break;
            case self::TYPE_PRODUCT:
                $model = 'CategoriesForProducts';
                break;
            default:
                throw new ServiceException('Invalid type of category', self::ERROR_INVALID_LINK_TYPE);
        }
        return 'App\Models\\'.$model;
    }*/

    public function createNewLinkObject($link_type){
        switch ($link_type) {
            case self::TYPE_LINK_COMPANY_CATEGORY_SERVICE:
                $object = new CompaniesCategories();
                break;
            case self::TYPE_LINK_USER_CATEGORY_SERVICE:
                $object = new UsersCategories();
                break;
            default:
                throw new ServiceException('Invalid link type', self::ERROR_INVALID_LINK_TYPE);
        }
        return $object;
    }

    public function fillLinkObject($link_type, $object, $data){
        switch ($link_type) {
            case self::TYPE_LINK_COMPANY_CATEGORY_SERVICE:
                $object->setCompanyId($data['company_id']);
                $object->setCategoryId($data['category_id']);
                break;
            case self::TYPE_LINK_USER_CATEGORY_SERVICE:
                $object->setUserId($data['user_id']);
                $object->setCategoryId($data['category_id']);
                break;
            default:
                throw new ServiceException('Invalid link type', self::ERROR_INVALID_LINK_TYPE);
        }
        return $object;
    }

    public function linkObjects($link_type, $data, $exceptionIfExists = true){
        $linkObject = $this->createNewLinkObject($link_type);
        $linkObject = $this->fillLinkObject($link_type,$linkObject,$data);

        if ($exceptionIfExists?(!$linkObject->create()):(!$linkObject->save())) {
            $errors = SupportClass::getArrayWithErrors($linkObject);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to link company with category',
                    self::ERROR_UNABlE_LINK_CATEGORY_WITH_COMPANY, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to link company with category',
                    self::ERROR_UNABlE_LINK_CATEGORY_WITH_COMPANY);
            }
        }
        return $linkObject;
    }
}
