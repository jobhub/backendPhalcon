<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;


/**
 * Class CommentsAPIController
 * Контроллер, который содержит методы для работы с комментариями.
 * Комментирии на фотографии пользователей и новости.
 */
class CommentsAPIController extends Controller
{
    /**
     * Возвращает комментарии к указанной фотографии пользователя
     *
     * @method GET
     *
     * @param $imageId
     *
     * @return string - json array массив комментариев
     */
    public function getCommentsForImageAction($imageId)
    {
        if ($this->request->isGet()) {
            $response = new Response();

            $comments = CommentsImagesUsers::getComments($imageId);

            $response->setJsonContent($comments);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет комментарий к фотографии пользователя
     * @access private
     *
     * @method POST
     *
     * @params objectid - id изображения
     * @params commenttext - текст комментария
     * @params replyid (не обязательное) - id комментария, на который оставляется ответ.
     * @params accountid (не обязательное) - если не указано, значит от имени пользователя - аккаунта по умолчанию.
     *
     * @return string - json array в формате Status + id созданного комментария
     */
    public function addCommentForImageAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();
            $comment = new CommentsImagesUsers();
            if($this->request->getPost('accountid')!=null){
                if (!Accounts::checkUserHavePermission($userId, $this->request->getPost('accountid'), 'addNew')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $comment->setAccountId($this->request->getPost('accountid'));
            } else{
                $account = Accounts::findForUserDefaultAccount($userId);

                if(!$account){
                    $response->setJsonContent(
                        [
                            "status" => STATUS_UNRESOLVED_ERROR,
                            "errors" => ['аккаунт для указанного пользователя не создан, хотя такого быть не должно']
                        ]
                    );
                    return $response;
                }

                $comment->setAccountId($account->getId());
            }

            $comment
                ->setCommentDate(date('Y-m-d H:i:s'))
                ->setCommentText($this->request->getPost('commenttext'))
                ->setImageId($this->request->getPost('objectid'))
                ->setReplyId($this->request->getPost('replyid'));

            if(!$comment->save()){
                return SupportClass::getResponseWithErrors($comment);
            }

            $response->setJsonContent(
                ['status' => STATUS_OK,
                    'commentid' => $comment->getCommentId()]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Удаляет комментарий, оставленный к фотографии пользователя
     *
     * @method DELETE
     *
     * @param $commentId int id комментария
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteCommentForImageAction($commentId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $comment = CommentsImagesUsers::findFirstByCommentid($commentId);

            if (!$comment) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный id, комментарий не существует'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!Accounts::checkUserHavePermission($userId, $comment->getAccountId(),'deleteImage')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$comment->delete()) {
                return SupportClass::getResponseWithErrors($comment);
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Меняет лайкнутость текущим пользователем указанного комментария.
     * @params commentId - int id комментария
     * @params accountId - int id аккаунта, от имени которого совершается данное действие
     * (если не указан, значит берется по умолчанию для пользователя)
     *
     * @return Response
     */
    public function toggleLikeCommentForImageAction(){
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $comment = CommentsImagesUsers::findFirstByCommentid($this->request->getPost('commentId'));

            if (!$comment) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный id, комментарий не существует'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if(!$this->request->getPost('accountId')){
                $accountId = Accounts::findForUserDefaultAccount($userId)->getId();
            } else{
                $accountId = $this->request->getPost('accountId');
            }

            if (!Accounts::checkUserHavePermission($userId, $accountId,'toggleLikes')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $like = LikesCommentsImagesUsers::findCommentLiked($accountId,
                                                               $this->request->getPost('commentId'));

            if($like){
                if(!$like->delete()){
                    return SupportClass::getResponseWithErrors($like);
                }
            } else{
                $like = LikesCommentsImagesUsers::findCommentLikedByCompany($accountId,
                    $this->request->getPost('commentId'));

                if($like){
                    if(!$like->delete()){
                        return SupportClass::getResponseWithErrors($like);
                    }
                } else {

                    $like = new LikesCommentsImagesUsers();
                    $like->setAccountId($accountId)
                        ->setCommentid($this->request->getPost('commentId'));

                    if (!$like->save()) {
                        return SupportClass::getResponseWithErrors($like);
                    }
                }
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}