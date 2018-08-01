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

            if ($binderType == 0) {
                $binder = Tasks::findFirstByTaskid($binderId);
            } else
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
     * Получает отзывы.
     *
     * @method GET
     *
     * @param $subjectId
     * @param $subjectType
     *
     * @return string
     */
    public function getReviewsAction($subjectId, $subjectType)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();
            /*
             * $query = $this->modelsManager->createQuery("SELECT reviews.reviewId as id,
              reviews.textReview as text 
              FROM reviews inner join tasks 
              ON (reviews.binderId = tasks.taskId AND reviews.binderType = 0 AND reviews.executor = true) 
              WHERE tasks.subjectId = :subjectId: AND tasks.subjectType = :subjectType:");

            $query2 = $this->modelsManager->createQuery("SELECT reviews.reviewId as id, 
              reviews.textReview as text 
              FROM reviews inner join offers 
              ON (reviews.binderId = offers.taskId AND reviews.binderType = 0
                  AND reviews.executor = false AND offers.selected = true) 
              WHERE offers.subjectId = :subjectId: AND offers.subjectType = :subjectType:");

            $query3 = $this->modelsManager->createQuery("SELECT reviews.reviewId as id, 
              reviews.textReview as text 
              FROM reviews inner join requests
              ON (reviews.binderId = requests.requestId AND reviews.binderType = 1
                  AND reviews.executor = true)
              WHERE requests.subjectId = :subjectId: AND requests.subjectType = :subjectType:");

            $query4 = $this->modelsManager->createQuery("SELECT reviews.reviewId as id, 
              reviews.textReview as text 
              FROM services inner join requests ON (requests.serviceId = services.serviceId)
              inner join reviews
              ON (reviews.binderId = requests.requestId AND reviews.binderType = 1
                  AND reviews.executor = false)
              WHERE services.subjectId = :subjectId: AND services.subjectType = :subjectType:");

            $reviews  = $query->execute(
                [
                    'subjectId' => $userId,
                    'subjectType' => 0,
                ]
            );

            $reviews2  = $query2->execute(
                [
                    'subjectId' => $userId,
                    'subjectType' => 0,
                ]
            );

            $reviews3  = $query3->execute(
                [
                    'subjectId' => $userId,
                    'subjectType' => 0,
                ]
            );

            $reviews4  = $query4->execute(
                [
                    'subjectId' => $userId,
                    'subjectType' => 0,
                ]
            );

            $reviews_arr = [];

            foreach($reviews as $review)
                $reviews_arr[] = $review;

            foreach($reviews2 as $review)
                $reviews_arr[] = $review;

            foreach($reviews3 as $review)
                $reviews_arr[] = $review;

            foreach($reviews4 as $review)
                $reviews_arr[] = $review;*/

            //if(!SubjectsWithNotDeleted::checkUserHavePermission($userId,$subjectId,$subjectType, 'getReviews'))

            $query = $this->db->prepare("Select * FROM (
              --Отзывы оставленные на заказы данного субъекта
              (SELECT reviews.reviewId as id,
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor
              FROM reviews inner join tasks 
              ON (reviews.binderid= tasks.taskId AND reviews.bindertype = 'task' AND reviews.executor = true)
              WHERE tasks.subjectId = :subjectId AND tasks.subjectType = :subjectType)
              UNION
              --Отзывы оставленные на предложения данного субъекта
              (SELECT reviews.reviewId as id, 
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews inner join offers 
              ON (reviews.binderId = offers.taskId AND reviews.binderType = 'task'
                  AND reviews.executor = false AND offers.selected = true) 
              WHERE offers.subjectId = :subjectId AND offers.subjectType = :subjectType) 
              UNION
              --Отзывы оставленные на заявки
              (SELECT reviews.reviewId as id, 
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews inner join requests
              ON (reviews.binderId = requests.requestId AND reviews.binderType = 'request'
                  AND reviews.executor = true)
              WHERE requests.subjectId = :subjectId AND requests.subjectType = :subjectType) 
              UNION
              --Отзывы оставленные на услуги
              (SELECT reviews.reviewId as id, 
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM services inner join requests ON (requests.serviceId = services.serviceId)
              inner join reviews
              ON (reviews.binderId = requests.requestId AND reviews.binderType = 'request'
                  AND reviews.executor = false)
              WHERE services.subjectId = :subjectId AND services.subjectType = :subjectType)
              UNION
              --фейковые отзывы
              (SELECT reviews.reviewId as id, 
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews
              WHERE reviews.objectId = :subjectId AND reviews.objectType = :subjectType)
              ) p0
              ORDER BY p0.date desc"
            );

            $query->execute([
                'subjectId' => $subjectId,
                'subjectType' => $subjectType,
            ]);

            $reviews = $query->fetchAll(\PDO::FETCH_ASSOC);

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