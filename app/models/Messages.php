<?php

class Messages extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $messageId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $auctionId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $input;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $message;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date;

    /**
     * Method to set the value of field messageId
     *
     * @param integer $messageId
     * @return $this
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;

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
     * Method to set the value of field input
     *
     * @param integer $input
     * @return $this
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Method to set the value of field message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Method to set the value of field date
     *
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Returns the value of field messageId
     *
     * @return integer
     */
    public function getMessageId()
    {
        return $this->messageId;
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
     * Returns the value of field input
     *
     * @return integer
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Returns the value of field message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the value of field date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("messages");
        $this->belongsTo('auctionId', '\Auctions', 'auctionId', ['alias' => 'Auctions']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'messages';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Messages[]|Messages|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Messages|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
