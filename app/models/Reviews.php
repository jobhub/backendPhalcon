<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Reviews extends NotDeletedModelWithCascade
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $reviewid;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $textreview;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $reviewdate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $rating;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $fake;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $binderid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $bindertype;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $executor;

    /**
     * Method to set the value of field reviewId
     *
     * @param integer $reviewid
     * @return $this
     */
    public function setReviewId($reviewid)
    {
        $this->reviewid = $reviewid;

        return $this;
    }

    /**
     * Method to set the value of field textReview
     *
     * @param string $textreview
     * @return $this
     */
    public function setTextReview($textreview)
    {
        $this->textreview = $textreview;

        return $this;
    }

    /**
     * Method to set the value of field reviewDate
     *
     * @param string $reviewdate
     * @return $this
     */
    public function setReviewDate($reviewdate)
    {
        $this->reviewdate = $reviewdate;

        return $this;
    }

    /**
     * Method to set the value of field rating
     *
     * @param integer $rating
     * @return $this
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Method to set the value of field fake
     *
     * @param string $fake
     * @return $this
     */
    public function setFake($fake)
    {
        $this->fake = $fake;

        return $this;
    }

    /**
     * Method to set the value of field binderId
     *
     * @param integer $binderid
     * @return $this
     */
    public function setBinderId($binderid)
    {
        $this->binderid = $binderid;

        return $this;
    }

    /**
     * Method to set the value of field binderType
     *
     * @param integer $bindertype
     * @return $this
     */
    public function setBinderType($bindertype)
    {
        $this->bindertype = $bindertype;

        return $this;
    }

    /**
     * Method to set the value of field executor
     *
     * @param string $executor
     * @return $this
     */
    public function setExecutor($executor)
    {
        $this->executor = $executor;

        return $this;
    }

    /**
     * Returns the value of field idReview
     *
     * @return integer
     */
    public function getReviewId()
    {
        return $this->reviewid;
    }

    /**
     * Returns the value of field textReview
     *
     * @return string
     */
    public function getTextReview()
    {
        return $this->textreview;
    }

    /**
     * Returns the value of field reviewDate
     *
     * @return string
     */
    public function getReviewDate()
    {
        return $this->reviewdate;
    }

    /**
     * Returns the value of field rating
     *
     * @return integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Returns the value of field fake
     *
     * @return string
     */
    public function getFake()
    {
        return $this->fake;
    }

    /**
     * Returns the value of field binderId
     *
     * @return integer
     */
    public function getBinderId()
    {
        return $this->binderid;
    }

    /**
     * Returns the value of field binderType
     *
     * @return integer
     */
    public function getBinderType()
    {
        return $this->bindertype;
    }

    /**
     * Returns the value of field executor
     *
     * @return string
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if ($this->getFake() == null || !$this->getFake())
            $validator->add(
                'binderid',
                new Callback(
                    [
                        "message" => "Такой субъект не существует",
                        "callback" => function ($review) {
                            return Binders::checkBinderExists($review->getBinderId(), $review->getBinderType());
                        }
                    ]
                )
            );
        $validator->add(
            'rating',
            new Callback(
                [
                    "message" => "Рейтинг должен быть от 0 до 10",
                    "callback" => function ($review) {
                        return $review->getRating() <= 10 && $review->getRating() >= 0;
                    }
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("reviews");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'reviews';
    }

}
