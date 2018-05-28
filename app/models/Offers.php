<?php

class Offers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $offerId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $auctionId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deadline;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $selected;

    /**
     * Method to set the value of field offerId
     *
     * @param integer $offerId
     * @return $this
     */
    public function setOfferId($offerId)
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * Method to set the value of field userId
     *
     * @param integer $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
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
     * Method to set the value of field deadline
     *
     * @param string $deadline
     * @return $this
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field price
     *
     * @param integer $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }
    /**
     * Method to set the value of field selected
     *
     * @param integer $selected
     * @return $this
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Returns the value of field offerId
     *
     * @return integer
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Returns the value of field auctionId
     *
     * @return integer
     */
    public function getAuctionId()
    {
        return $this->auctionId;
    }

    /**
     * Returns the value of field deadline
     *
     * @return string
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the value of field selected
     *
     * @return integer
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("offers");
        $this->hasOne('offerId', 'Auctions', 'selectedOffer', ['alias' => 'Auctions']);
        $this->belongsTo('auctionId', '\Auctions', 'auctionId', ['alias' => 'Auctions']);
        $this->belongsTo('userId', '\Users', 'userId', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'offers';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Offers[]|Offers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Offers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function getScore()
    {
        $C=$this->users->getFinishedTasks();
        $R=$this->users->userInfo->getRaitingExecutor();
        $categoryId=(int)$this->auctions->tasks->getCategoryId();
        $r=$this->users->getRatingForCategory($categoryId);
        $ctask=(double)$this->auctions->tasks->getPrice()/$this->getPrice();
        $t=1;

        $otime=strtotime($this->getDeadline())/3600;
        $ttime=strtotime($this->auctions->tasks->getDeadline())/3600;
        $atime=strtotime($this->auctions->getDateEnd())/3600;

        $deltat=$ttime-$atime;
        if($otime<$ttime)
        {
            if($ttime-$otime<$deltat/2)
                $t=1.5;
            else
            {
                $t=1.25;
            }
          //  $t=(double)($ttime-$otime) /*/($otime-$atime)*/;
        }
        else
        {
            if($otime-$ttime>$deltat/2)
                $t=0.5;
            else
                $t=0.75;
         //  $t=($otime-$ttime) /*/($otime-$atime)*/;
        }
        $S=0.1*$C*(0.5*$R+$r+5)*$ctask*$t;
        return $S;
    }

}
