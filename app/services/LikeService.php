<?php

namespace App\Services;

use App\Models\CommentsImagesUsers;
use App\Models\CommentsModel;
use App\Models\CommentsNews;
use App\Models\CompanyRole;
use App\Models\LikesCommentsImagesUsers;
use App\Models\LikesCommentsNews;
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

    const TYPE_COMMENT_USER_IMAGES = 0;
    const TYPE_COMMENT_NEWS = 1;

    const ADDED_CODE_NUMBER = 14000;

    /** Unable to create user */
    const ERROR_UNABLE_DELETE_LIKE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_LIKE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_LIKE_TYPE = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_LIKE_NOT_FOUND = 4 + self::ADDED_CODE_NUMBER;

    public function createLike(int $object_id, int $accountId, int $type)
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
    }

    public function getLikeByIds(int $object_id, int $accountId, int $type)
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
    }

    public function deleteLike($like)
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
    }

    public function toggleLike(int $object_id, int $accountId, int $type){
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
    }
}
