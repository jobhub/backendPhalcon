<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesReviews extends ImagesModel
{
    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'object_id',
            new Callback(
                [
                    "message" => "Такой отзыв не существует",
                    "callback" => function ($image) {
                        $service = Reviews::findFirstByReviewId($image->getObjectId());
                        if ($service)
                            return true;
                        return false;
                    }
                ]
            )
        );

        return parent::validation()&&$this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("images_reviews");
        $this->belongsTo('object_id', '\Reviews', 'review_id', ['alias' => 'Reviews']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'images_reviews';
    }

    public function getSequenceName()
    {
        return "imagesreviews_imageid_seq";
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesReviews[]|ImagesReviews|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesReviews|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * return non formatted images objects
     * @param $reviewId
     * @param $page
     * @param $page_size
     * @return mixed
     */
    public static function findImagesForReview($reviewId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE){
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        return self::handleImages(
            self::find(['conditions'=>'object_id = :reviewId:','bind'=>['reviewId'=>$reviewId],
                'limit'=>$page_size,'offset'=>$offset])->toArray()
        );
    }
}
