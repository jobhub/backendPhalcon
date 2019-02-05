<?php

namespace App\Controllers;


use App\Libs\SupportClass;
use App\Models\AccountModel;
use App\Models\CommentsModel;
use App\Services\AccountService;
use App\Services\LikeService;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Models\CommentsNews;
use App\Models\CommentsImagesUsers;
use App\Models\Accounts;

use App\Services\CommentService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http404Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
use PhpParser\Comment;

/**
 * Class CommentsAPIController
 * Контроллер, который содержит методы для работы с комментариями.
 * Комментирии на фотографии пользователей и новости.
 */
class CommentsAPIController extends AbstractController
{
    /**
     * Возвращает комментарии к указанной фотографии пользователя
     *
     * @method GET
     *
     * @param $image_id
     *
     * @return string - json array массив комментариев
     */
    /*public function getCommentsForImageAction($image_id)
    {
        return CommentsImagesUsers::getComments($image_id);
    }*/

    /**
     * Добавляет комментарий к фотографии пользователя
     * @access private
     *
     * @method POST
     *
     * @params object_id - id изображения
     * @params comment_text - текст комментария
     * @params reply_id (не обязательное) - id комментария, на который оставляется ответ.
     * @params account_id (не обязательное) - если не указано, значит от имени пользователя - аккаунта по умолчанию.
     *
     * @return string - json array в формате Status + id созданного комментария
     */
    /*public function addCommentForImageAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['image_id'] = $inputData->object_id;
        $data['comment_text'] = $inputData->comment_text;
        $data['reply_id'] = $inputData->reply_id;
        $data['account_id'] = $inputData->account_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addComment')) {
                throw new Http403Exception('Permission error');
            }

            $data['comment_date'] = date('Y-m-d H:i:s');

            $comment = $this->commentService->createComment($data, CommentService::TYPE_USER_IMAGES);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_UNABLE_CREATE_COMMENT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case CommentService::ERROR_INVALID_COMMENT_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Comment was successfully created', ['comment' => $comment->toArray()]);
    }*/

    /**
     * Удаляет комментарий, оставленный к фотографии пользователя
     *
     * @method DELETE
     *
     * @param $comment_id int id комментария
     *
     * @return string - json array в формате Status - результат операции
     */
    /*public function deleteCommentForImageAction($comment_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $comment = $this->commentService->getCommentById($comment_id, CommentService::TYPE_USER_IMAGES);

            if (!Accounts::checkUserHavePermission($userId, $comment->getAccountId(), 'deleteComment')) {
                throw new Http403Exception('Permission error');
            }

            $this->commentService->deleteComment($comment);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_UNABLE_DELETE_COMMENT:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_COMMENT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Comment was successfully deleted');
    }*/

    /**
     * Меняет лайкнутость текущим пользователем указанного комментария.
     *
     * @method POST
     *
     * @params comment_id - int id комментария
     * @params account_id - int id аккаунта, от имени которого совершается данное действие
     * (если не указан, значит берется по умолчанию для пользователя)
     *
     * @return Response
     */
    /*public function toggleLikeCommentForImageAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['comment_id'] = $inputData->comment_id;
        $data['account_id'] = $inputData->account_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if (empty(trim($data['account_id']))) {
                $data['account_id'] = Accounts::findForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'deleteComment')) {
                throw new Http403Exception('Permission error');
            }

            $liked = $this->likeService->toggleLike($data['comment_id'], $data['account_id'], LikeService::TYPE_COMMENT_USER_IMAGES);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case LikeService::ERROR_UNABLE_CREATE_LIKE:
                case LikeService::ERROR_UNABLE_DELETE_LIKE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case LikeService::ERROR_INVALID_LIKE_TYPE:
                    throw new Http500Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Like was toggled', ['liked' => $liked]);
    }*/

    /**
     * Возвращает комментарии к указанной новости
     *
     * @method GET
     *
     * @param $news_id
     *
     * @return string - json array массив комментариев
     */
    /*public function getCommentsForNewsAction($news_id)
    {
        return CommentsNews::getComments($news_id);
    }*/

    /**
     * Добавляет комментарий к новости
     * @access private
     *
     * @method POST
     *
     * @params object_id - id новости
     * @params comment_text - текст комментария
     * @params account_id - int id аккаунта, от имени которого добавляется комментарий.
     * Если не указан, то от имени текущего пользователя по умолчанию.
     *
     * @return string - json array в формате Status - результат операции
     */
    /*public function addCommentForNewsAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['news_id'] = $inputData->object_id;
        $data['comment_text'] = $inputData->comment_text;
        $data['reply_id'] = $inputData->reply_id;
        $data['account_id'] = $inputData->account_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addComment')) {
                throw new Http403Exception('Permission error');
            }

            $data['comment_date'] = date('Y-m-d H:i:s');

            $comment = $this->commentService->createComment($data, CommentService::TYPE_NEWS);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_UNABLE_CREATE_COMMENT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case CommentService::ERROR_INVALID_COMMENT_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Comment was successfully created', ['comment' => $comment->toArray()]);
    }*/

    /**
     * Удаляет комментарий, оставленный к фотографии пользователя
     *
     * @method DELETE
     *
     * @param $comment_id int id комментария
     *
     * @return string - json array в формате Status - результат операции
     */
    /*public function deleteCommentForNewsAction($comment_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $comment = $this->commentService->getCommentById($comment_id, CommentService::TYPE_NEWS);

            if (!Accounts::checkUserHavePermission($userId, $comment->getAccountId(), 'deleteComment')) {
                throw new Http403Exception('Permission error');
            }

            $this->commentService->deleteComment($comment);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_UNABLE_DELETE_COMMENT:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_COMMENT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Comment was successfully deleted');
    }*/

    /**
     * Меняет лайкнутость текущим пользователем указанного комментария.
     *
     * @method POST
     *
     * @params comment_id - int id комментария
     * @params account_id - int id аккаунта, от имени которого совершается данное действие
     * (если не указан, значит берется по умолчанию для пользователя)
     *
     * @return Response
     */
    /*public function toggleLikeCommentForNewsAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['comment_id'] = $inputData->comment_id;
        $data['account_id'] = $inputData->account_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if (empty(trim($data['account_id']))) {
                $data['account_id'] = Accounts::findForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'deleteComment')) {
                throw new Http403Exception('Permission error');
            }

            $liked = $this->likeService->toggleLike($data['comment_id'], $data['account_id'], LikeService::TYPE_COMMENT_NEWS);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case LikeService::ERROR_UNABLE_CREATE_LIKE:
                case LikeService::ERROR_UNABLE_DELETE_LIKE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case LikeService::ERROR_INVALID_LIKE_TYPE:
                    throw new Http500Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Like was toggled', ['liked' => $liked]);
    }*/

    /**
     * Добавляет комментарий к указанному объекту
     * @access private
     *
     * @method POST
     *
     * @param $type
     *
     * @params object_id - id новости
     * @params comment_text - текст комментария
     * @params account_id - int id аккаунта, от имени которого добавляется комментарий.
     * Если не указан, то от имени текущего пользователя по умолчанию.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addCommentAction($type)
    {
        $inputData = $this->request->getJsonRawBody();
        $data['object_id'] = $inputData->object_id;
        $data['comment_text'] = $inputData->comment_text;
        $data['reply_id'] = $inputData->reply_id;
        $data['account_id'] = $inputData->account_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addComment')) {
                throw new Http403Exception('Permission error');
            }

            $data['comment_date'] = date('Y-m-d H:i:sO');

            $comment = $this->commentService->createComment($data, $type);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_UNABLE_CREATE_COMMENT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case CommentService::ERROR_INVALID_COMMENT_TYPE:
                    $exception = new Http404Exception(
                        _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                        new \Exception('URI not found: ' .
                            $this->request->getMethod() . ' ' . $this->request->getURI())
                    );
                    throw $exception;
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Comment was successfully created', ['comment' => $comment->toArray()]);
    }

    /**
     * Удаляет комментарий указаннного типа
     *
     * @method DELETE
     *
     * @param $type string - тип комментария
     * @param $comment_id int id комментария
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteCommentAction($type, $comment_id)
    {
        try {
            $userId = self::getUserId();

            $comment = $this->commentService->getCommentById($comment_id, $type);

            if (!Accounts::checkUserHavePermission($userId, $comment->getAccountId(), 'deleteComment')) {
                throw new Http403Exception('Permission error');
            }

            $this->commentService->deleteComment($comment);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_UNABLE_DELETE_COMMENT:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_COMMENT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case CommentService::ERROR_INVALID_COMMENT_TYPE:
                    $exception = new Http404Exception(
                        _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                        new \Exception('URI not found: ' .
                            $this->request->getMethod() . ' ' . $this->request->getURI())
                    );
                    throw $exception;
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Comment was successfully deleted');
    }

    /**
     * Возвращает комментарии к указанному объекту
     *
     * @method GET
     * @param $type
     * @param $object_id
     * @param $parent_id
     * @param $account_id
     * @param $page
     * @param $page_size
     * @return string - json array массив комментариев
     */
    public function getCommentsAction($type, $object_id, $parent_id = null, $account_id = null, $page = 1, $page_size = CommentsModel::DEFAULT_RESULT_PER_PAGE_PARENT)
    {
        try {
            if (self::isAuthorized()) {
                $userId = self::getUserId();
                if ($account_id == null || !SupportClass::checkInteger($account_id)) {
                    $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
                }


                if (!Accounts::checkUserHavePermission($userId, $account_id, 'getComments')) {
                    throw new Http403Exception('Permission error');
                }

            } else
                $account_id = null;


            if ($parent_id != null && SupportClass::checkInteger($parent_id)) {
                $comments = $this->commentService->getChildComments($object_id, $type, $parent_id,$account_id, $page, $page_size);
            } else {
                $comments = $this->commentService->getParentComments($object_id, $type,$account_id, $page, $page_size);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CommentService::ERROR_INVALID_COMMENT_TYPE:
                    $exception = new Http404Exception(
                        _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                        new \Exception('URI not found: ' .
                            $this->request->getMethod() . ' ' . $this->request->getURI())
                    );
                    throw $exception;
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return $comments;
    }
}