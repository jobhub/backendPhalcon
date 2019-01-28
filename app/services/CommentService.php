<?php

namespace App\Services;

use App\Models\CommentsImagesUsers;
use App\Models\CommentsModel;
use App\Models\CommentsNews;
use App\Models\CommentsServices;
use App\Models\CompanyRole;
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
class CommentService extends AbstractService {

    const TYPE_USER_IMAGES = 'user-image';
    const TYPE_NEWS = 'news';
    const TYPE_SERVICES = 'services';

    const ADDED_CODE_NUMBER = 13000;

    /** Unable to create user */
    const ERROR_COMMENT_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_COMMENT = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_COMMENT = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_COMMENT = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_COMMENT_TYPE = 5 + self::ADDED_CODE_NUMBER;

    public function createComment(array $commentData, $type){
        switch ($type) {
            case self::TYPE_USER_IMAGES:
                $comment = new CommentsImagesUsers();
                break;
            case self::TYPE_NEWS:
                $comment = new CommentsNews();
                break;
            case self::TYPE_SERVICES:
                $comment = new CommentsServices();
                break;
            default:
                throw new ServiceException('Invalid type of comment', self::ERROR_INVALID_COMMENT_TYPE);
        }

        $this->fillComment($comment,$commentData,$type);

        if ($comment->create() == false) {
            $errors = SupportClass::getArrayWithErrors($comment);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable create comment',
                    self::ERROR_UNABLE_CREATE_COMMENT,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable create comment',
                    self::ERROR_UNABLE_CREATE_COMMENT);
            }
        }

        return $comment;
    }

    public function changeComment(CommentsModel $comment, array $commentData, $type){
        $this->fillComment($comment,$commentData,$type);
        if ($comment->update() == false) {
            $errors = SupportClass::getArrayWithErrors($comment);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable change comment',
                    self::ERROR_UNABLE_CHANGE_COMMENT,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable change comment',
                    self::ERROR_UNABLE_CHANGE_COMMENT);
            }
        }
        return $comment;
    }

    private function fillComment(CommentsModel $comment, array $data, $type){
        if(!empty(trim($data['account_id'])))
            $comment->setAccountId($data['account_id']);
        if(!empty(trim($data['reply_id'])))
            $comment->setReplyId($data['reply_id']);
        if(!empty(trim($data['comment_text'])))
            $comment->setCommentText($data['comment_text']);
        if(!empty(trim($data['comment_text'])))
            $comment->setCommentText($data['comment_text']);
        if(!empty(trim($data['comment_date'])))
            $comment->setCommentDate(date('Y-m-d H:i:sO', strtotime($data['comment_date'])));
        if(!empty(trim($data['object_id'])))
            $comment->setObjectId($data['object_id']);
    }

    public function getCommentById(int $commentId, $type){
        switch ($type) {
            case self::TYPE_USER_IMAGES:
                $comment = CommentsImagesUsers::findFirstByCommentId($commentId);
                break;
            case self::TYPE_NEWS:
                $comment = CommentsNews::findFirstByCommentId($commentId);
                break;
            case self::TYPE_SERVICES:
                $comment = CommentsServices::findFirstByCommentId($commentId);
                break;
            default:
                throw new ServiceException('Invalid type of comment', self::ERROR_INVALID_COMMENT_TYPE);
        }

        if (!$comment) {
            throw new ServiceException('Comment don\'t exists', self::ERROR_COMMENT_NOT_FOUND);
        }
        return $comment;
    }

    public function getParentComments(int $objectId, $type,$accountId, $page = 1, $page_size = CommentsModel::DEFAULT_RESULT_PER_PAGE_PARENT){
        switch ($type) {
            case self::TYPE_USER_IMAGES:
                $model = 'CommentsImagesUsers';
                break;
            case self::TYPE_NEWS:
                $model = 'CommentsNews';
                break;
            case self::TYPE_SERVICES:
                $model = 'CommentsServices';
                break;
            default:
                throw new ServiceException('Invalid type of comment', self::ERROR_INVALID_COMMENT_TYPE);
        }

        $comments = CommentsModel::findParentComments($model,$objectId,$page,$page_size,$accountId);

        return $comments;
    }

    public function getChildComments(int $objectId, $type, $parentId,$accountId, $page = 1, $page_size = CommentsModel::DEFAULT_RESULT_PER_PAGE_PARENT){
        switch ($type) {
            case self::TYPE_USER_IMAGES:
                $model = 'CommentsImagesUsers';
                break;
            case self::TYPE_NEWS:
                $model = 'CommentsNews';
                break;
            case self::TYPE_SERVICES:
                $model = 'CommentsServices';
                break;
            default:
                throw new ServiceException('Invalid type of comment', self::ERROR_INVALID_COMMENT_TYPE);
        }

        $comments = CommentsModel::findChildComments($model,$objectId,$parentId,$page,$page_size,$accountId);

        return $comments;
    }

    public function deleteComment(CommentsModel $comment){
        try{
        if (!$comment->delete()) {
            $errors = SupportClass::getArrayWithErrors($comment);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete comment',
                    self::ERROR_UNABLE_DELETE_COMMENT, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete comment',
                    self::ERROR_UNABLE_DELETE_COMMENT);
            }
        }
        }catch(\PDOException $e){
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
    }
}
