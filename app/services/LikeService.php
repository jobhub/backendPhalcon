<?php

namespace App\Services;

use App\Models\CommentsImagesUsers;
use App\Models\CommentsModel;
use App\Models\CommentsNews;
use App\Models\CompanyRole;
use App\Models\LikeModel;
use App\Models\LikesCommentsImagesUsers;
use App\Models\LikesCommentsNews;
use App\Models\Accounts;
use Phalcon\DI\FactoryDefault as DI;

use App\Models\Companies;

use App\Libs\SupportClass;
use App\Libs\ImageLoader;
//models
use App\Models\Userinfo;
use App\Models\Settings;
use App\Controllers\HttpExceptions\Http500Exception;

/**
 * business logic for users
 *
 * Class UsersService
 */
class LikeService extends AbstractService
{
    const TYPE_COMMENT_USER_IMAGES = 'comment-image-user';
    const TYPE_COMMENT_NEWS = 'comment-news';
    const TYPE_COMMENT_SERVICE = 'comment-service';
    const TYPE_NEWS = 'news';
    const TYPE_SERVICE = 'service';
    const TYPE_USER_IMAGE = 'image-user';

    const ADDED_CODE_NUMBER = 5000;

    /** Unable to create user */
    const ERROR_UNABLE_DELETE_LIKE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_LIKE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_LIKE_TYPE = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_LIKE_NOT_FOUND = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_LIKE_OBJECT_NOT_FOUND = 5 + self::ADDED_CODE_NUMBER;

    /*public function createLike(int $object_id, int $accountId, $type)
    {
        switch ($type) {
            case self::TYPE_COMMENT_USER_IMAGES:
                $like = new LikesCommentsImagesUsers();
                $like->setCommentId($object_id);
                break;
            case self::TYPE_COMMENT_NEWS:
                $like = new LikesCommentsNews();
                $like->setCommentId($object_id);
                break;
            default:
                throw new ServiceException('Invalid type of like', self::ERROR_INVALID_LIKE_TYPE);
        }

        $like->setAccountId($accountId);

        if ($like->create() == false) {
            $errors = SupportClass::getArrayWithErrors($like);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable create like',
                    self::ERROR_UNABLE_CREATE_LIKE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable create like',
                    self::ERROR_UNABLE_CREATE_LIKE);
            }
        }

        return $like;
    }*/

    /*public function getLikeByIds(int $object_id, int $accountId, $type)
    {
        switch ($type) {
            case self::TYPE_COMMENT_USER_IMAGES:
                $like = LikesCommentsImagesUsers::findCommentLiked($accountId, $object_id);
                break;
            case self::TYPE_COMMENT_NEWS:
                $like = LikesCommentsNews::findCommentLiked($accountId, $object_id);
                break;
            default:
                throw new ServiceException('Invalid type of like', self::ERROR_INVALID_LIKE_TYPE);
        }

        if (!$like) {
            throw new ServiceException('Object did not liked', self::ERROR_LIKE_NOT_FOUND);
        }
        return $like;
    }*/

    /*public function deleteLike($like)
    {
        try {
            if (!$like->delete()) {
                $errors = SupportClass::getArrayWithErrors($like);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to delete like',
                        self::ERROR_UNABLE_DELETE_LIKE, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to delete like',
                        self::ERROR_UNABLE_DELETE_LIKE);
                }
            }
        } catch (\PDOException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
    }*/

    /*public function toggleLike(int $object_id, int $accountId, $type){
        switch ($type) {
            case self::TYPE_COMMENT_USER_IMAGES:
                $like = LikesCommentsImagesUsers::findCommentLiked($accountId, $object_id);
                break;
            case self::TYPE_COMMENT_NEWS:
                $like = LikesCommentsNews::findCommentLiked($accountId, $object_id);
                break;
            default:
                throw new ServiceException('Invalid type of like', self::ERROR_INVALID_LIKE_TYPE);
        }

        if(!$like){
            $this->createLike($object_id, $accountId,$type);
            $liked = true;
        } else{
            $this->deleteLike($like);
            $liked = false;
        }

        return $liked;
    }*/

    public static function deleteLikeFromObject($model, $name_id, $object_id, Accounts $account)
    {
        if ($account->getCompanyId() == null) {
            $accounts = [$account->getId()];
        } else {
            $accounts_obj = Accounts::findByCompanyId($account->getCompanyId());
            $accounts = [];
            foreach ($accounts_obj as $account) {
                $accounts[] = $account->getId();
            }
        }

        $object_arr = $model::findFirst([$name_id . ' = :objectId:', 'bind' => ['objectId' => $object_id]]);

        $likes = SupportClass::translateInPhpArrFromPostgreArr($object_arr->getLikes());

        $res_likes = array_diff($likes,$accounts);

        $object_arr->setLikes(SupportClass::to_pg_array($res_likes));

        if (!$object_arr->update()) {
            $errors = SupportClass::getArrayWithErrors($object_arr);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete like',
                    self::ERROR_UNABLE_DELETE_LIKE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete like',
                    self::ERROR_UNABLE_DELETE_LIKE);
            }
        }
    }

    public static function addLikeToObject($model, $name_id, $object_id, Accounts $account)
    {
        $object_arr = $model::findFirst([$name_id . ' = :objectId:', 'bind' => ['objectId' => $object_id]]);

        $likes = $object_arr->getLikes();

        $likes = SupportClass::translateInPhpArrFromPostgreArr($likes);

        $likes[] = $account->getId();

        $object_arr->setLikes(SupportClass::to_pg_array($likes));

        if (!$object_arr->update()) {
            $errors = SupportClass::getArrayWithErrors($object_arr);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to add like',
                    self::ERROR_UNABLE_CREATE_LIKE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to add like',
                    self::ERROR_UNABLE_CREATE_LIKE);
            }
        }
    }

    public static function toggleArrayLike($type, $object_id, Accounts $account)
    {
        switch ($type) {
            case self::TYPE_COMMENT_USER_IMAGES:
                $model = 'App\Models\CommentsImagesUsers';
                $name_id = 'comment_id';
                break;
            case self::TYPE_COMMENT_NEWS:
                $model = 'App\Models\CommentsNews';
                $name_id = 'comment_id';
                break;
            case self::TYPE_COMMENT_SERVICE:
                $model = 'App\Models\CommentsServices';
                $name_id = 'comment_id';
                break;
            case self::TYPE_USER_IMAGE:
                $model = 'App\Models\ImagesUsers';
                $name_id = 'image_id';
                break;
            case self::TYPE_NEWS:
                $model = 'App\Models\News';
                $name_id = 'news_id';
                break;
            case self::TYPE_SERVICE:
                $model = 'App\Models\Services';
                $name_id = 'service_id';
                break;
            default:
                throw new ServiceException('Invalid type of like', self::ERROR_INVALID_LIKE_TYPE);
        }

        $object = $model::findFirst([$name_id.' = :objectId:','bind'=>['objectId'=>$object_id]]);

        if(!$object){
            throw new ServiceException('Object for like not found', self::ERROR_LIKE_OBJECT_NOT_FOUND);
        }

        if (LikeModel::getObjectLikedByAccount($model, $name_id, $object_id, $account)) {
            self::deleteLikeFromObject($model, $name_id, $object_id, $account);
            return false;
        } else {
            self::addLikeToObject($model, $name_id, $object_id, $account);
            return true;
        }
    }

    public static function toggleLikeToObject($model, $name_id, $object_id, Accounts $account)
    {
        if (LikeModel::getObjectLikedByAccount($model, $name_id, $object_id, $account)) {
            self::deleteLikeFromObject($model, $name_id, $object_id, $account);
        } else {
            self::addLikeToObject($model, $name_id, $object_id, $account);
        }
    }
}
