<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 02.05.2018
 * Time: 11:45
 */

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class ReviewsAPIController extends Controller
{
    /**
     * Добавляет отзыв.
     *
     * @method POST
     *
     * @params int binderId, int binderType, bool executor, int rating
     * @params (Необязатальные) textReview, fake.
     *
     * @return Response - Status
     */
    public function addReviewAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $response = new Response();
            $binderId = $this->request->getPost('binderId');
            $binderType = $this->request->getPost('binderType');
            $executor = $this->request->getPost('executor');

            if (!Binders::checkUserHavePermission($userId, $binderId,
                $binderType, $executor, 'addReview')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $review = Reviews::findFirst(['binderid = :binderId: AND bindertype = :binderType: AND executor = :executor:',
                'bind' => ['binderId' => $binderId, 'binderType' => $binderType, 'executor' => $executor]]);

            if ($review) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Отзыв в связи с заказом уже написан']
                    ]
                );
                return $response;
            }

            if ($binderType == 'task') {
                $binder = Tasks::findFirstByTaskid($binderId);
            } elseif($binderType == 'request')
                $binder = Requests::findFirstByRequestid($binderId);

            if (!($binder->getStatus() == STATUS_CANCELED ||
                $binder->getStatus() == STATUS_NOT_EXECUTED ||
                $binder->getStatus() == STATUS_REJECTED_BY_SYSTEM ||
                $binder->getStatus() == STATUS_PAID_EXECUTOR ||
                $binder->getStatus() == STATUS_PAID_BY_SECURE_TRANSACTION)) {

                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя писать отзыв на данном этапе']
                    ]
                );
                return $response;
            }

            $review = new Reviews();

            $review->setBinderId($binderId);
            $review->setBinderType($binderType);
            $review->setExecutor($executor);
            $review->setTextReview($this->request->getPost('textReview'));
            $review->setReviewDate(date('Y-m-d H:i:s'));
            $review->setUserId($userId);

            /*if($this->request->getPost('fake'))
                $review->setFake($this->request->getPost('fake'));*/

            $review->setRating($this->request->getPost('rating'));

            if (!$review->save()) {
                $errors = [];
                foreach ($review->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
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
     * Редактирует отзыв.
     *
     * @method PUT
     *
     * @params int rating, reviewId
     * @param (Необязатальные) textReview.
     *
     * @return Response - Status
     */
    public function editReviewAction()
    {
        if ($this->request->isPut()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $review = Reviews::findFirstByReviewid($this->request->getPut('reviewId'));

            $response = new Response();

            if (!$review) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Отзыв не существует']
                    ]
                );
                return $response;
            }

            if (!Binders::checkUserHavePermission($userId, $review->getBinderId(),
                $review->getBinderType(), $review->getExecutor(), 'editReview')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $review->setTextReview($this->request->getPut('textReview'));
            $review->setReviewDate(date('Y-m-d H:i:s'));
            $review->setRating($this->request->getPut('rating'));
            $review->setUserId($userId);

            if (!$review->update()) {
                $errors = [];
                foreach ($review->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
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
     * Удаляет отзыв.
     *
     * @method DELETE
     *
     * @param $reviewId
     *
     * @return Response - Status
     */
    public function deleteReviewAction($reviewId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $review = Reviews::findFirstByReviewid($reviewId);

            $response = new Response();

            if (!$review) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Отзыв не существует']
                    ]
                );
                return $response;
            }

            if (!Binders::checkUserHavePermission($userId, $review->getBinderId(),
                $review->getBinderType(), $review->getExecutor(), 'editReview')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$review->delete()) {
                $errors = [];
                foreach ($review->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
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
     * Возвращает отзывы об указанном субъекте, будь то пользователь или компания.
     *
     * @method GET
     *
     * @param $subjectId - id субъекта
     * @param $subjectType - тип субъекта
     *
     * @return string - json array [status,[reviews]]
     */
    public function getReviewsForSubjectAction($subjectId, $subjectType)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $reviews = Reviews::getReviewsForObject($subjectId,$subjectType);

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    "reviews" => $reviews
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Возвращает отзывы, связанные с указанной услугой.
     *
     * @method GET
     *
     * @param $serviceId - id слуги
     *
     * @return string - json array [status,[reviews]]
     */
    public function getReviewsForServiceAction($serviceId)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $reviews = Reviews::getReviewsForService($serviceId);

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    "reviews" => $reviews
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     *
     */
    public function addTypeAction(){
        $query = $this->db->prepare("CREATE TYPE bindertype AS ENUM ('task', 'request');"
        );

        return $query->execute();
    }

}