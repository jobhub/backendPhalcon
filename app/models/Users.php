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
    protected $userId;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    protected $email;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    protected $phone;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    protected $password;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $role;

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
     * Method to set the value of field email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Method to set the value of field phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Method to set the value of field password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = sha1($password);

        return $this;
    }

    /**
     * Method to set the value of field role
     *
     * @param string $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;

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
     * Returns the value of field email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the value of field phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Returns the value of field password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the value of field role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

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
                    'message' => 'Введите, пожалуйста, правильный адрес',
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
        $this->hasMany('userId', 'Logs', 'userId', ['alias' => 'Logs']);
        $this->hasMany('userId', 'Offers', 'userId', ['alias' => 'Offers']);
        $this->hasMany('userId', 'Tasks', 'userId', ['alias' => 'Tasks']);
        $this->hasOne('userId', 'Userinfo', 'userId', ['alias' => 'Userinfo']);
        $this->hasMany('userId', 'Reviews','userId_subject', ['alias'=>'Reviews']);
        $this->hasMany('userId', 'Reviews','userId_object', ['alias'=>'Reviews']);


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

    public function getFinishedTasks()
    {
        $query = $this->modelsManager->createQuery('SELECT COUNT(*) AS c FROM offers, auctions, tasks, users WHERE offers.userId=users.userId AND users.userId=:userId: AND auctions.selectedOffer=offers.offerId AND tasks.taskId=auctions.taskId AND tasks.status=\'Завершено\'');
        $count = $query->execute(
            [
                'userId' => "$this->userId",
            ]
        );
        $count=$count[0]['c'];
        return $count;
    }
    public function getRatingForCategory($idCategory)
    {
        $query=$this->getModelsManager()->createQuery('SELECT AVG(reviews.raiting) AS a FROM reviews, auctions, tasks, users WHERE tasks.categoryId=:categoryId: AND tasks.taskId=auctions.taskId AND auctions.auctionId=reviews.auctionId AND reviews.userId_object=:userId: AND reviews.executor=1');
        $avg = $query->execute(
            [
                'userId' => "$this->userId",
                'categoryId'=>$idCategory
            ]
        );
        $avg=$avg[0]['a'];
        return $avg;
    }
}
