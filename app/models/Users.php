<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

class Users extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $userId;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $email;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $phone;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    public $password;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $role;

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model'   => $this,
                    'message' => 'Please enter a correct email address',
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("users");
        $this->hasMany('userId', 'Favoritecategories', 'userId', ['alias' => 'Favoritecategories']);
        $this->hasMany('userId', 'Offers', 'userId', ['alias' => 'Offers']);
        $this->hasMany('userId', 'Tasks', 'userId', ['alias' => 'Tasks']);
        $this->hasMany('userId', 'Userinfo', 'userId', ['alias' => 'Userinfo']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'users';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users[]|Users|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
