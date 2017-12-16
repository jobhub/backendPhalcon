<?php

class Settings extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $searchRadius;

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
     * Method to set the value of field searchRadius
     *
     * @param integer $searchRadius
     * @return $this
     */
    public function setSearchRadius($searchRadius)
    {
        if($searchRadius == ""){
            $searchRadius = null;
        }
        else {
            $this->searchRadius = $searchRadius;
        }

        return $this;
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
     * Returns the value of field searchRadius
     *
     * @return integer
     */
    public function getSearchRadius()
    {

        return $this->searchRadius;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("settings");
        $this->hasOne('userId', '\Userinfo', 'userId', ['alias' => 'Userinfo']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'settings';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Settings[]|Settings|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Settings|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
