<?php

class FavoriteUsers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userSubject;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userObject;

    /**
     * Method to set the value of field userSubject
     *
     * @param integer $userSubject
     * @return $this
     */
    public function setUserSubject($userSubject)
    {
        $this->userSubject = $userSubject;

        return $this;
    }

    /**
     * Method to set the value of field userObject
     *
     * @param integer $userObject
     * @return $this
     */
    public function setUserObject($userObject)
    {
        $this->userObject = $userObject;

        return $this;
    }

    /**
     * Returns the value of field userSubject
     *
     * @return integer
     */
    public function getUserSubject()
    {
        return $this->userSubject;
    }

    /**
     * Returns the value of field userObject
     *
     * @return integer
     */
    public function getUserObject()
    {
        return $this->userObject;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("favoriteUsers");
        $this->belongsTo('userObject', '\Users', 'userId', ['alias' => 'Users']);
        $this->belongsTo('userSubject', '\Users', 'userId', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favoriteUsers';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoriteusers[]|Favoriteusers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoriteusers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
