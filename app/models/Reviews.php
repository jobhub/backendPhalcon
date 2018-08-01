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
     * @Column(type="string", length=10, nullable=false)
     */
    protected $bindertype;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $executor;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    //куча костылей для того, чтобы можно было писать фейковые отзывы. Жесть
    protected $subjectid;
    protected $subjecttype;
    protected $objectid;
    protected $objecttype;

    /**
     * Методы-костыли
    */

    public function setSubjectId($subjectid)
    {
        $this->subjectid = $subjectid;

        return $this;
    }
    public function setSubjectType($subjecttype)
    {
        $this->subjecttype = $subjecttype;

        return $this;
    }

    public function setObjectId($objectid)
    {
        $this->objectid = $objectid;

        return $this;
    }
    public function setObjectType($objecttype)
    {
        $this->objecttype = $objecttype;

        return $this;
    }

    public function getSubjectId()
    {
        return $this->subjectid;
    }

    public function getSubjectType()
    {
        return $this->subjecttype;
    }

    public function getObjectId()
    {
        return $this->objectid;
    }

    public function getObjectType()
    {
        return $this->objecttype;
    }

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
        if($bindertype == 0)
            $this->bindertype = 'task';
        else if($bindertype == 1)
            $this->bindertype = 'request';
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
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserId($userid)
    {
        $this->userid = $userid;

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
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userid;
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
                        "message" => "Такой объект не существует",
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

        if ($this->getFake() == null || !$this->getFake())
            $validator->add(
                'userid',
                new Callback(
                    [
                        "message" => "Такой пользователь не существует",
                        "callback" => function ($review) {
                            if($review->users!=null)
                                return true;
                            return false;
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
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
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
