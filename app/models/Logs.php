<?php

class Logs extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $logId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $userId;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    protected $controller;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    protected $action;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date;

    /**
     * Method to set the value of field logId
     *
     * @param integer $logId
     * @return $this
     */
    public function setLogId($logId)
    {
        $this->logId = $logId;

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
     * Method to set the value of field controller
     *
     * @param string $controller
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Method to set the value of field action
     *
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

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
     * Returns the value of field logId
     *
     * @return integer
     */
    public function getLogId()
    {
        return $this->logId;
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
     * Returns the value of field controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Returns the value of field action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
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
        $this->setSource("logs");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Logs[]|Logs|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Logs|\Phalcon\Mvc\Model\ResultInterface
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
        return 'logs';
    }

}
