<?php

class Userinfo extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $userId;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $firstname;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $patronymic;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $lastname;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $birthday;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $male;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    public $address;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $about;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $executor;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("userinfo");
        $this->hasMany('userId', 'Settings', 'userId', ['alias' => 'Settings']);
        $this->belongsTo('userId', '\Users', 'userId', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'userinfo';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userinfo[]|Userinfo|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userinfo|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
