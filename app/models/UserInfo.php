<?php

class UserInfo extends \Phalcon\Mvc\Model
{

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
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("userinfo");
        $this->hasMany('user_id', 'Settings', 'user_id', ['alias' => 'Settings']);
        $this->belongsTo('user_id', '\Users', 'user_id', ['alias' => 'Users']);
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
     * @return UserInfo[]|UserInfo|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserInfo|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
