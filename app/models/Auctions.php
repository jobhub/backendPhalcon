<?php

class Auctions extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
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
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $selectedOffer;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $dateStart;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $dateEnd;

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
     * Method to set the value of field selectedOffer
     *
     * @param integer $selectedOffer
     * @return $this
     */
    public function setSelectedOffer($selectedOffer)
    {
        $this->selectedOffer = $selectedOffer;

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
     * Returns the value of field auctionId
     *
     * @return integer
     */
    public function getAuctionId()
    {
        return $this->auctionId;
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
     * Returns the value of field selectedOffer
     *
     * @return integer
     */
    public function getSelectedOffer()
    {
        return $this->selectedOffer;
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
     * Initialize method for model.
     */

    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("auctions");
        $this->hasOne('selectedOffer', '\Offers', 'offerId', ['alias' => 'Offers']);
        $this->belongsTo('taskId', '\Tasks', 'taskId', ['alias' => 'Tasks']);
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
