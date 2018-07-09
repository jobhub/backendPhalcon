<?php

class Messages extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $messageId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $message;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $date;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userIdObject;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userIdSubject;

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
     * Method to set the value of field userIdObject
     *
     * @param integer $userIdObject
     * @return $this
     */
    public function setUserIdObject($userIdObject)
    {
        $this->userIdObject = $userIdObject;

        return $this;
    }

    /**
     * Method to set the value of field userIdSubject
     *
     * @param integer $userIdSubject
     * @return $this
     */
    public function setUserIdSubject($userIdSubject)
    {
        $this->userIdSubject = $userIdSubject;

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
     * Returns the value of field userIdObject
     *
     * @return integer
     */
    public function getUserIdObject()
    {
        return $this->userIdObject;
    }

    /**
     * Returns the value of field userIdSubject
     *
     * @return integer
     */
    public function getUserIdSubject()
    {
        return $this->userIdSubject;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("messages");
        $this->belongsTo('userIdObject', '\Users', 'userId', ['alias' => 'Users']);
        $this->belongsTo('userIdSubject', '\Users', 'userId', ['alias' => 'Users']);
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
