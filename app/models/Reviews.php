<?php

class Reviews extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
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
     * @Column(type="string", nullable=true)
     */
    protected $reviewDate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=true)
     */
    protected $executor;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userId_object;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userId_subject;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $raiting;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $auctionId;

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
     * Method to set the value of field userId_object
     *
     * @param integer $userId_object
     * @return $this
     */
    public function setUserIdObject($userId_object)
    {
        $this->userId_object = $userId_object;

        return $this;
    }

    /**
     * Method to set the value of field userId_subject
     *
     * @param integer $userId_subject
     * @return $this
     */
    public function setUserIdSubject($userId_subject)
    {
        $this->userId_subject = $userId_subject;

        return $this;
    }

    /**
     * Method to set the value of field raiting
     *
     * @param integer $raiting
     * @return $this
     */
    public function setRaiting($raiting)
    {
        $this->raiting = $raiting;

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
     * Returns the value of field userId_object
     *
     * @return integer
     */
    public function getUserIdObject()
    {
        return $this->userId_object;
    }

    /**
     * Returns the value of field userId_subject
     *
     * @return integer
     */
    public function getUserIdSubject()
    {
        return $this->userId_subject;
    }

    /**
     * Returns the value of field raiting
     *
     * @return integer
     */
    public function getRaiting()
    {
        return $this->raiting;
    }

    /**
     * Method to set the value of field auctionId
     *
     * @param integer $auctionId
     * @return $this
     */
    public function setAuctionId($auctionId)
    {
        $this->auctionId = $auctionId;

        return $this;
    }

    /**
     * Method to get the value of field auctionId
     *
     * @return int
     */
    public function getAuctionId()
    {
        return $this->auctionId;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("reviews");
        $this->belongsTo('userId_object', '\Users', 'userId', ['alias' => 'Users']);
        $this->belongsTo('userId_subject', '\Users', 'userId', ['alias' => 'Users']);
        $this->belongsTo('auctionId', '\Auctions', 'auctionId', ['alias' => 'Auctions']);
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

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Reviews[]|Reviews|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    public function save($data = null, $whiteList = null)
    {
        parent::save($data, $whiteList);

        $reviews = Reviews::find(["userId_object = :userId_object: and executor = :executor:",
            "bind" => ["userId_object" => $this->getUserIdObject(),"executor"=>$this->getExecutor()]]);
        $userinfo = Userinfo::findFirstByUserId($this->getUserIdObject());

        if($this->getExecutor() == 1)
            $sum = $userinfo->getRaitingExecutor();
        else
            $sum = $userinfo->getRaitingClient();

        $sum = (($this->getRaiting() * ($reviews->count()+4)) + $sum)/($reviews->count()+5);

        if($this->getExecutor() == 1)
            $userinfo->setRaitingExecutor($sum);
        else
            $userinfo->setRaitingClient($sum);

        $userinfo->save();
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Reviews|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
