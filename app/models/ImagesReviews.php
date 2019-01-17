<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesReviews extends ImagesModel
{
    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $review_id;

    const MAX_IMAGES = 3;

    /**
     * Method to set the value of field reviewid
     *
     * @param integer $review_id
     * @return $this
     */
    public function setReviewId($review_id)
    {
        $this->review_id = $review_id;

        return $this;
    }

    /**
     * Returns the value of field reviewid
     *
     * @return integer
     */
    public function getReviewId()
    {
        return $this->review_id;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'review_id',
            new Callback(
                [
                    "message" => "Такой отзыв не существует",
                    "callback" => function ($image) {
                        $service = Reviews::findFirstByReviewId($image->getReviewId());
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
        $this->setSource("imagesreviews");
        $this->belongsTo('review_id', '\Reviews', 'review_id', ['alias' => 'Reviews']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'imagesreviews';
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
     * @return mixed
     */
    public static function findImagesForReview($reviewId){
        return self::findByReviewId($reviewId);
    }
}
