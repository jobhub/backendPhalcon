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
    public function indexAction($userId)
    {
        if ($this->request->isGet()) {
            //$today = date("Y-m-d");
            $query = $this->modelsManager->createQuery('SELECT * FROM reviews INNER JOIN userinfo ON reviews.userId_subject=userinfo.userId 
                WHERE reviews.userId_object = :userId:');

            $reviews = $query->execute(
                [
                    'userId' => "$userId"
                ]
            );
            return json_encode($reviews);
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function addReviewAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $response = new Response();

            $auction = Auctions::findFirstByAuctionId($this->request->getPost("tenderId"));
            $offer = Offers::findFirst(["auctionId =:auctionId: and selected = 1",
                'bind' => [
                    'auctionId' => $auction->getAuctionId()
                ]
            ]);

            if ($userId == $auction->tasks->getUserId()) {
                $reviews = Reviews::find(["auctionId =:auctionId: and executor = :executor:",
                        'bind' => [
                            'auctionId' => $auction->getAuctionId(),
                            'executor' => 1
                        ]
                    ]
                );
                if ($reviews->count() == 0) {
                    //Значит, еще не написал

                    $review = new Reviews();
                    $review->setAuctionId($auction->getAuctionId());
                    $review->setExecutor(1);
                    $review->setRaiting($this->request->getPost("rating"));
                    $review->setReviewDate(date('Y-m-d H:i:s'));
                    $review->setTextReview($this->request->getPost("textReview"));
                    $review->setUserIdSubject($userId);


                    $review->setUserIdObject($offer->getUserId());

                    if (!$review->save()) {
                        foreach ($review->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }

                        $response->setJsonContent(
                            [
                                "errors" => $errors,
                                "status" => "WRONG_DATA"
                            ]
                        );

                        return $response;
                    }

                    $response->setJsonContent(
                        [
                            "status" => "OK"
                        ]
                    );
                    return $response;
                } else {
                    $response->setJsonContent(
                        [
                            "status" => "WRONG_DATA"
                        ]
                    );
                    return $response;
                }
            } else if ($offer->getUserId() == $userId) {
                //Значит он исполнитель, пишет о заказчике
                $reviews = Reviews::find(["auctionId =:auctionId: and executor = :executor:",
                        'bind' => [
                            'auctionId' => $auction->getAuctionId(),
                            'executor' => 0
                        ]
                    ]
                );
                if ($reviews->count() == 0) {
                    $review = new Reviews();
                    $review->setAuctionId($auction->getAuctionId());
                    $review->setExecutor(0);
                    $review->setRaiting($this->request->getPost("rating"));
                    $review->setReviewDate(date('Y-m-d H:i:s'));
                    $review->setTextReview($this->request->getPost("textReview"));
                    $review->setUserIdSubject($userId);


                    $review->setUserIdObject($auction->tasks->getUserId());

                    if (!$review->save()) {
                        foreach ($review->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }

                        $response->setJsonContent(
                            [
                                "errors" => $errors,
                                "status" => "WRONG_DATA"
                            ]
                        );

                        return $response;
                    }

                    $response->setJsonContent(
                        [
                            "status" => "OK"
                        ]
                    );
                    return $response;
                } else {
                    $response->setJsonContent(
                        [
                            "status" => "WRONG_DATA"
                        ]
                    );
                    return $response;
                }
            } else {
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA"
                    ]
                );
                return $response;
            }

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}