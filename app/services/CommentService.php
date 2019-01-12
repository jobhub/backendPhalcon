<?php

namespace App\Services;

use App\Models\CommentsImagesUsers;
use App\Models\CommentsModel;
use App\Models\CommentsNews;
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

    const TYPE_USER_IMAGES = 0;
    const TYPE_NEWS = 1;

    const ADDED_CODE_NUMBER = 13000;

    /** Unable to create user */
    const ERROR_COMMENT_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_COMMENT = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_COMMENT = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_COMMENT = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_COMMENT_TYPE = 5 + self::ADDED_CODE_NUMBER;

    public function createComment(array $commentData, int $type){
        switch ($type) {
            case self::TYPE_USER_IMAGES:
                $comment = new CommentsImagesUsers();
                break;
            case self::TYPE_NEWS:
                $comment = new CommentsNews();
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

    public function changeComment(CommentsModel $comment, array $commentData, int $type){
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

    private function fillComment(CommentsModel $comment, array $data, int $type){
        if(!empty(trim($data['account_id'])))
            $comment->setAccountId($data['account_id']);
        if(!empty(trim($data['reply_id'])))
            $comment->setReplyId($data['reply_id']);
        if(!empty(trim($data['comment_text'])))
            $comment->setCommentText($data['comment_text']);
        if(!empty(trim($data['comment_text'])))
            $comment->setCommentText($data['comment_text']);
        if(!empty(trim($data['comment_date'])))
            $comment->setCommentDate(date('Y-m-d H:i:s', strtotime($data['comment_date'])));

        switch ($type) {
            case self::TYPE_USER_IMAGES:
                if(!empty(trim($data['image_id'])))
                    $comment->setImageId($data['image_id']);
                break;
            case self::TYPE_NEWS:
                if(!empty(trim($data['news_id'])))
                    $comment->setNewsId($data['news_id']);
                break;
            default:
                throw new ServiceException('Invalid type of comment', self::ERROR_INVALID_COMMENT_TYPE);
        }
    }

    public function getCommentById(int $commentId, int $type){
        switch ($type) {
            case self::TYPE_USER_IMAGES:
                $comment = CommentsImagesUsers::findFirstByCommentId($commentId);
                break;
            case self::TYPE_NEWS:
                $comment = CommentsNews::findFirstByCommentId($commentId);
                break;
            default:
                throw new ServiceException('Invalid type of comment', self::ERROR_INVALID_COMMENT_TYPE);
        }

        if (!$comment) {
            throw new ServiceException('Comment don\'t exists', self::ERROR_COMMENT_NOT_FOUND);
        }
        return $comment;
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
