<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
class Reviews extends NotDeletedModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $idReview;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $textReview;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $reviewDate;

    /**
     *
     * @var integer
     * @Column(type="string", nullable=false)
     */
    protected $executor;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $objectId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectId;

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
    protected $objectType;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectType;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deleted;

    /**
     * Method to set the value of field idReview
     *
     * @param integer $idReview
     * @return $this
     */
    public function setIdReview($idReview)
    {
        $this->idReview = $idReview;

        return $this;
    }

    /**
     * Method to set the value of field textReview
     *
     * @param string $textReview
     * @return $this
     */
    public function setTextReview($textReview)
    {
        $this->textReview = $textReview;

        return $this;
    }

    /**
     * Method to set the value of field reviewDate
     *
     * @param string $reviewDate
     * @return $this
     */
    public function setReviewDate($reviewDate)
    {
        $this->reviewDate = $reviewDate;

        return $this;
    }

    /**
     * Method to set the value of field executor
     *
     * @param integer $executor
     * @return $this
     */
    public function setExecutor($executor)
    {
        $this->executor = $executor;

        return $this;
    }

    /**
     * Method to set the value of field objectId
     *
     * @param integer $objectId
     * @return $this
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Method to set the value of field subjectId
     *
     * @param integer $subjectId
     * @return $this
     */
    public function setSubjectId($subjectId)
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    /**
     * Method to set the value of field raiting
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
     * Method to set the value of field objectType
     *
     * @param integer $objectType
     * @return $this
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * Method to set the value of field subjectType
     *
     * @param integer $subjectType
     * @return $this
     */
    public function setSubjectType($subjectType)
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    /**
     * Returns the value of field idReview
     *
     * @return integer
     */
    public function getIdReview()
    {
        return $this->idReview;
    }

    /**
     * Returns the value of field textReview
     *
     * @return string
     */
    public function getTextReview()
    {
        return $this->textReview;
    }

    /**
     * Returns the value of field reviewDate
     *
     * @return string
     */
    public function getReviewDate()
    {
        return $this->reviewDate;
    }

    /**
     * Returns the value of field executor
     *
     * @return integer
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     * Returns the value of field objectId
     *
     * @return integer
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * Returns the value of field raiting
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
     * Returns the value of field objectType
     *
     * @return integer
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * Returns the value of field subjectType
     *
     * @return integer
     */
    public function getSubjectType()
    {
        return $this->subjectType;
    }

    /**
     * Method to set the value of field deleted
     *
     * @param string $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
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
            'subjectId',
            new Callback(
                [
                    "message" => "Такой субъект не существует",
                    "callback" => function ($service) {
                        return Subjects::checkSubjectExists($service->getSubjectId(), $service->getSubjectType());
                    }
                ]
            )
        );

        $validator->add(
            'objectId',
            new Callback(
                [
                    "message" => "Объект отзыва не существует",
                    "callback" => function ($service) {
                        return Subjects::checkSubjectExists($service->getObjectId(), $service->getObjectType());
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
