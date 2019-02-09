<?php

namespace App\Services;

use App\Libs\SupportClass;

use App\Models\FavoriteCategories;
use App\Models\Categories;
use App\Models\CompaniesCategories;
use App\Models\UsersCategories;

/**
 * business logic for users
 *
 * Class UsersService
 */
class CategoryService extends AbstractService
{
    const ADDED_CODE_NUMBER = 6000;

    const ERROR_ALREADY_SIGNED = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_DON_NOT_SIGNED = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABlE_SUBSCRIBE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABlE_CHANGE_RADIUS = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABlE_UNSUBSCRIBE = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_CATEGORY_NOT_FOUND = 6 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABlE_LINK_CATEGORY_WITH_COMPANY = 7 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABlE_LINK_CATEGORY_WITH_USER = 8 + self::ADDED_CODE_NUMBER;

    /*public function setFavourite(int $userId, int $categoryId, $radius)
    {
        $fav = FavoriteCategories::findByIds($userId, $categoryId);
        if ($fav) {
            throw new ServiceException('User already signed on this category',
                self::ERROR_ALREADY_SIGNED);
        }

        $fav = new FavoriteCategories();
        $fav->setCategoryId($categoryId);
        $fav->setUserId($userId);
        $fav->setRadius($radius);

        if (!$fav->save()) {
            $errors = SupportClass::getArrayWithErrors($fav);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to sign user to category',
                    self::ERROR_UNABlE_SUBSCRIBE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to sign user to category',
                    self::ERROR_UNABlE_SUBSCRIBE);
            }
        }

        return $fav;
    }*/

    public function editRadius(int $accountId, int $categoryId, $radius)
    {
        $fav = FavoriteCategories::findByIds('App\Models\FavoriteCategories',$accountId, $categoryId);

        if (!$fav) {
            throw new ServiceException('Account don\'t signed on this category',
                self::ERROR_DON_NOT_SIGNED);
        }

        $fav->setRadius($radius);

        if (!$fav->update()) {
            $errors = SupportClass::getArrayWithErrors($fav);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to change radius for category',
                    self::ERROR_UNABlE_CHANGE_RADIUS, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to change radius for category',
                    self::ERROR_UNABlE_CHANGE_RADIUS);
            }
        }
    }

    /*public function deleteFavourite(int $userId, int $categoryId)
    {
        $fav = FavoriteCategories::findByIds($userId, $categoryId);

        if (!$fav) {
            return true;
        }

        if (!$fav->delete()) {
            $errors = SupportClass::getArrayWithErrors($fav);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to unsubscribe user',
                    self::ERROR_UNABlE_UNSUBSCRIBE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to unsubscribe user',
                    self::ERROR_UNABlE_UNSUBSCRIBE);
            }
        }

        return true;
    }*/

    public function linkCompanyWithCategory($categoryId, $companyId){
        $category = $this->getCategoryById($categoryId);

        $companyCategory = new CompaniesCategories();
        $companyCategory->setCompanyId($companyId);
        $companyCategory->setCategoryId($category->getCategoryId());

        if (!$companyCategory->create()) {
            $errors = SupportClass::getArrayWithErrors($companyCategory);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to link company with category',
                    self::ERROR_UNABlE_LINK_CATEGORY_WITH_COMPANY, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to link company with category',
                    self::ERROR_UNABlE_LINK_CATEGORY_WITH_COMPANY);
            }
        }
        return $companyCategory;
    }

    public function linkUserWithCategory($categoryId, $userId){
        $category = $this->getCategoryById($categoryId);

        $userCategory = new UsersCategories();
        $userCategory->setUserId($userId);
        $userCategory->setCategoryId($category->getCategoryId());

        if (!$userCategory->create()) {
            $errors = SupportClass::getArrayWithErrors($userCategory);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to link user with category',
                    self::ERROR_UNABlE_LINK_CATEGORY_WITH_USER, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to link user with category',
                    self::ERROR_UNABlE_LINK_CATEGORY_WITH_USER);
            }
        }
        return $userCategory;
    }

    public function getCategoryById(int $categoryId){
        $category = Categories::findFirstByCategoryId($categoryId);

        if (!$category || $category == null) {
            throw new ServiceException('Category don\'t exists', self::ERROR_CATEGORY_NOT_FOUND);
        }
        return $category;
    }
}
