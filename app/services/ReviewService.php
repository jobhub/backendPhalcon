<?php

namespace App\Services;

use App\Models\Reviews;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ReviewService extends AbstractService
{
    const ADDED_CODE_NUMBER = 19000;

    const ERROR_UNABLE_CREATE_REVIEW = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_REVIEW_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_REVIEW = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_REVIEW = 4 + self::ADDED_CODE_NUMBER;


    public function createReview(array $reviewData)
    {
        $review = new Reviews();

        $this->fillReview($review, $reviewData);
        try {
            if ($review->create() == false) {
                SupportClass::getErrorsWithException($review, self::ERROR_UNABLE_CREATE_REVIEW, 'Unable to create review');
            }
        } catch (\PDOException $e) {
            throw new ServiceException('Unable to create review', self::ERROR_UNABLE_CREATE_REVIEW, $e);
        }

        return $review;
    }

    public function getReviewById(int $reviewId)
    {
        $review = Reviews::findFirstByReviewId($reviewId);

        if (!$review) {
            throw new ServiceException('Review don\'t exists', self::ERROR_REVIEW_NOT_FOUND);
        }
        return $review;
    }

    public function fillReview(Reviews $review, array $data)
    {
        if (!empty(trim($data['review_text'])))
            $review->setReviewText($data['review_text']);
        if (!empty(trim($data['review_date'])))
            $review->setReviewDate(date('Y-m-d H:i:s', strtotime($data['review_date'])));
        if (!empty(trim($data['rating'])))
            $review->setRating($data['rating']);
        if (!empty(trim($data['binder_id'])))
            $review->setBinderId($data['binder_id']);
        if (!empty(trim($data['binder_type'])))
            $review->setBinderType($data['binder_type']);
        if (!is_null($data['executor']))
            $review->setExecutor($data['executor']);

        $review->setFake(false);
    }

    public function deleteReview(Reviews $review)
    {
        if ($review->delete() == false) {
            SupportClass::getErrorsWithException($review, self::ERROR_UNABLE_DELETE_REVIEW, 'Unable to delete review');
        }

        return $review;
    }

    public function changeReview(Reviews $review, array $reviewData)
    {
        $this->fillReview($review, $reviewData);

        if ($review->update() == false) {
            SupportClass::getErrorsWithException($review, self::ERROR_UNABLE_CHANGE_REVIEW, 'Unable to change review');
        }

        return $review;
    }
}
