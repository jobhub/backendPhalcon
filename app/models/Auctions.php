<?php

class Auctions extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $auctionId;
    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $taskId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $dateStart;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $dateEnd;


    /**
     * Method to set the value of field taskId
     *
     * @param integer $taskId
     * @return $this
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * Method to set the value of field dateStart
     *
     * @param string $dateStart
     * @return $this
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Method to set the value of field dateEnd
     *
     * @param string $dateEnd
     * @return $this
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }


    /**
     * Returns the value of field taskId
     *
     * @return integer
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * Returns the value of field dateStart
     *
     * @return string
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Returns the value of field dateEnd
     *
     * @return string
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
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
        //$this->setSchema("public");
        $this->setSource("auctions");
        $this->hasMany('auctionId', 'Messages', 'auctionId', ['alias' => 'Messages']);
        $this->hasMany('auctionId', 'Offers', 'auctionId', ['alias' => 'Offers']);
        $this->belongsTo('selectedOffer', '\Offers', 'offerId', ['alias' => 'Offers']);
        $this->belongsTo('taskId', '\Tasks', 'taskId', ['alias' => 'Tasks']);
        $this->hasMany('auctionId', 'Reviews','auctionId', ['alias'=>'Reviews']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Auctions[]|Auctions|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    public function save($data = null, $whiteList = null)
    {
        $result = parent::save($data, $whiteList);

        if($result) {
            $new = new News();
            $new->setNewType(0);
            $new->setIdentify($this->getAuctionId());
            $new->setDate(date("Y-m-d H:i:s"));

            $new->save();
        }
        return $result;
    }

    public function delete($data = null, $whiteList = null)
    {
        $result = parent::delete($data, $whiteList);

        if($result) {
            $new = News::findFirst(["newType = 0 and identify = :identify:",
                "bind" => ["identify" => $this->getAuctionId()]]);

            if($new){
                $new->delete();
            }
        }
        return $result;
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Auctions|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'auctions';
    }

}
