<?php

namespace App\Models;

class ActivationCodes extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $user_id;
    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    protected $activation;
    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    protected $deactivation;
    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $time;
    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $used;
    /**
     * Минимальное время, которое должно пройти перед повторной отправкой.
     * В секундах.
     */
    const RESEND_TIME = 300;

    const TIME_LIFE = 3600;

    /**
     * Method to set the value of field userid
     *
     * @param integer $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }
    /**
     * Method to set the value of field activation
     *
     * @param string $activation
     * @return $this
     */
    public function setActivation($activation)
    {
        $this->activation = $activation;
        return $this;
    }
    /**
     * Method to set the value of field deactivation
     *
     * @param string $deactivation
     * @return $this
     */
    public function setDeactivation($deactivation)
    {
        $this->deactivation = $deactivation;
        return $this;
    }
    /**
     * Method to set the value of field time
     *
     * @param string $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }
    public function setUsed($used)
    {
        $this->used = $used;
        return $this;
    }
    /**
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    /**
     * Returns the value of field activation
     *
     * @return string
     */
    public function getActivation()
    {
        return $this->activation;
    }
    /**
     * Returns the value of field deactivation
     *
     * @return string
     */
    public function getDeactivation()
    {
        return $this->deactivation;
    }
    /**
     * Returns the value of field time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }
    public function getUsed()
    {
        return $this->used;
    }
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("activationcodes");
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'activationcodes';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ActivationCodes[]|ActivationCodes|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }
    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ActivationCodes|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}