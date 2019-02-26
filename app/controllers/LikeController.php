<?php

namespace App\Controllers;


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
class LikeController extends AbstractController
{
    /**
     * Меняет лайкнутость текущим пользователем указанного комментария.
     *
     * @method POST
     *
     * @param $type
     * @params object_id - int id комментария
     * @params account_id - int id аккаунта, от имени которого совершается данное действие
     * (если не указан, значит берется по умолчанию для пользователя)
     *
     * @return Response
     */
    public function toggleLikeAction($type)
    {
        $inputData = $this->request->getJsonRawBody();
        $data['object_id'] = $inputData->object_id;
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

            $account = $this->accountService->getAccountById($data['account_id']);

            $liked = $this->likeService->toggleArrayLike($type,$data['object_id'],$account);

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
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case LikeService::ERROR_LIKE_OBJECT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case LikeService::ERROR_INVALID_LIKE_TYPE:
                    throw new Http500Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Like was toggled',['liked'=>$liked]);
    }
}