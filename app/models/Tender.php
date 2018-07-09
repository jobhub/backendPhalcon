<?php

class Tender extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $tenderId;

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
     * Method to set the value of field tenderId
     *
     * @param integer $tenderId
     * @return $this
     */
    public function setTenderId($tenderId)
    {
        $this->tenderId = $tenderId;

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
     * Returns the value of field tenderId
     *
     * @return integer
     */
    public function getTenderId()
    {
        return $this->tenderId;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("service_services");
        $this->setSource("tender");
        $this->hasMany('tenderId', 'Messages', 'tenderId', ['alias' => 'Messages']);
        $this->hasMany('tenderId', 'Offers', 'tenderId', ['alias' => 'Offers']);
        $this->belongsTo('taskId', '\Tasks', 'taskId', ['alias' => 'Tasks']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Tender[]|Tender|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Tender|\Phalcon\Mvc\Model\ResultInterface
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
        return 'tender';
    }

}
