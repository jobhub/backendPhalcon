<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Users extends NotDeletedModel
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
     * @var integer
     * @Column(type="integer", nullable=false)
     */
    protected $phoneId;

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
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deleted;

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
     * Method to set the value of field phoneId
     *
     * @param integer $phoneId
     * @return $this
     */
    public function setPhoneId($phoneId)
    {
        $this->phoneId = $phoneId;

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
     * Returns the value of field phoneId
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phoneId;
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
     * Method to set the value of field deleted
     *
     * @param string $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
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

        $validator->add(
            'phoneId',
            new Callback(
                [
                    "message" => "Телефон не был создан",
                    "callback" => function($user) {
                        $phone = Phones::findFirstByPhoneId($user->getPhoneId());

                        if($phone)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'password',
            new Callback(
                [
                    "message" => "Пароль должен содержать не менее 6 символов",
                    "callback" => function($user) {

                        if($user->getPassword()!= null && strlen($user->getPassword()) >= 6)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'role',
            new PresenceOf(
                [
                    "message" => "Не указана роль пользователя",
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
        //$this->setSchema("service_services");
        $this->setSource("users");
        $this->hasMany('userId', 'Favoritecategories', 'userId', ['alias' => 'Favoritecategories']);
        $this->hasMany('userId', 'Logs', 'userId', ['alias' => 'Logs']);
        $this->hasOne('userId', 'Userinfo', 'userId', ['alias' => 'Userinfo']);
        $this->belongsTo('phoneId', 'Phones', 'phoneId', ['alias' => 'Phones']);
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

    public function getFinishedTasks()
    {
       // $query = $this->modelsManager->createQuery('SELECT COUNT(*) AS c FROM offers, auctions, tasks, users WHERE offers.userId=users.userId AND users.userId=:userId: AND auctions.selectedOffer=offers.offerId AND tasks.taskId=auctions.taskId AND tasks.status=\'Завершено\'');


        $query = $this->modelsManager->createQuery(
            'SELECT COUNT(*) AS c FROM offers INNER JOIN auctions ON offers.auctionId = auctions.auctionId
              INNER JOIN tasks ON auctions.taskId = auctions.taskId
              WHERE offers.userId = :userId: AND offers.selected = 1 AND tasks.status=\'Завершено\'');

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
