<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Userinfo extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    protected $first_name;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    protected $patronymic;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    protected $last_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $birthday;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $male;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $address;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $about;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=true)
     */
    protected $status;

    /**
     * @var integer
     * @Column(type="tyniint", length=4, nullable=false)
     */
    protected $rating_executor;
    protected $rating_client;
    protected $path_to_photo;

    protected $last_time;

    protected $email;

    protected $phones;

    const publicColumns = ['user_id', 'first_name', 'last_name', 'patronymic',
        'birthday', 'male', 'address', 'about', 'status', 'rating_executor', 'rating_client',
        'path_to_photo', 'last_time'];

    const publicColumnsInStr = 'user_id, first_name, last_name, patronymic,
        birthday, male, address, about, status, rating_executor, rating_client, path_to_photo, last_time';

    const shortColumns = ['user_id', 'first_name', 'last_name', 'path_to_photo'];

    const shortColumnsInStr = 'user_id, first_name, last_name, path_to_photo';

    /**
     * Method to set the value of field userId
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
     * Method to set the value of field firstname
     *
     * @param string $first_name
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;
    }



    /**
     * Method to set the value of field patronymic
     *
     * @param string $patronymic
     * @return $this
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    /**
     * Method to set the value of field lastname
     *
     * @param string $last_name
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * Method to set the value of field birthday
     *
     * @param string $birthday
     * @return $this
     */
    public function setBirthday($birthday)
    {
        if($birthday==="")
            $birthday=null;
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Method to set the value of field male
     *
     * @param integer $male
     * @return $this
     */
    public function setMale($male)
    {
        if($male == 'мужской' || $male == 'Мужской')
            $this->male = 1;
        else if($male == 'женский' || $male == 'Женский')
            $this->male = 0;
        else
            $this->male = $male;

        return $this;
    }

    /**
     * Method to set the value of field address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Method to set the value of field about
     *
     * @param string $about
     * @return $this
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Method to set the value of field about
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }


    public function setRatingExecutor($rating_executor)
    {
        $this->rating_executor = $rating_executor;

        return $this;
    }

    public function setRatingClient($rating_client)
    {
        $this->rating_client = $rating_client;

        return $this;
    }

    public function setPathToPhoto($path_to_photo)
    {
        $this->path_to_photo = $path_to_photo;

        return $this;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the value of field firstname
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Returns the value of field patronymic
     *
     * @return string
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * Returns the value of field lastname
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }


    public function getFIO()
    {
        return "$this->last_name $this->first_name $this->patronymic";
    }

    /**
     * Returns the value of field birthday
     *
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }



    /**
     * Returns the value of field male
     *
     * @return integer
     */
    public function getMale()
    {
        return $this->male;
    }

    /**
     * Returns the value of field address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Returns the value of field about
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    public function getRatingExecutor()
    {
        return $this->rating_executor;
    }
    public function getRatingClient()
    {
        return $this->rating_client;
    }
    public function getPathToPhoto()
    {
        return $this->path_to_photo;
    }

    /**
     * @return mixed
     */
    public function getLastTime()
    {
        return $this->last_time;
    }

    /**
     * @param mixed $last_time
     */
    public function setLastTime($last_time)
    {
        $this->last_time = $last_time;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
            'first_name',
            new PresenceOf(
                [
                    "message" => "Требуется указать имя пользователя",
                ]
            )
        );

        $validator->add(
            'last_name',
            new PresenceOf(
                [
                    "message" => "Требуется указать фамилию пользователя",
                ]
            )
        );

        $validator->add(
            'male',
            new PresenceOf(
                [
                    "message" => "Требуется указать пол",
                ]
            )
        );

        if($this->getPathToPhoto() != null)
            $validator->add(
                'path_to_photo',
                new Callback(
                    [
                        "message" => "Формат картинки не поддерживается",
                        "callback" => function ($user) {
                            $format = pathinfo($user->getPathToPhoto(), PATHINFO_EXTENSION);

                            if ($format == 'jpeg' || 'jpg')
                                return true;
                            elseif ($format == 'png')
                                return true;
                            elseif ($format == 'gif')
                                return true;
                            else {
                                return false;
                            }
                        }
                    ]
                )
            );

        if($this->getEmail() != null)
        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model' => $this,
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
        //$this->setSchema("service_services");
        $this->setSource("userinfo");
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

    public static function findUserInfoById(int $userId, array $columns = null){
        if($columns == null)
            return self::findFirst(['user_id = :userId:',
                'bind' => ['userId' => $userId]]);
        else{
            return self::findFirst(['columns' => $columns,'user_id = :userId:',
                'bind' => ['userId' => $userId]]);
        }
    }

    public static function handleUserInfo(Userinfo $userInfo){

        $phones = PhonesUsers::getUserPhones($userInfo->getUserId());
        $images = ImagesUsers::getImages($userInfo->getUserId());

        $countNews = count(News::findByAccount(
            Accounts::findForUserDefaultAccount($userInfo->getUserId())->getId()
        ));
        $countSubscribers = count(FavoriteUsers::findByUserObject($userInfo->getUserId()));
        $countSubscriptions = count(FavoriteUsers::findByUserSubject($userInfo->getUserId()))
            + count(FavoriteCompanies::findByUserId($userInfo->getUserId()));

        return [
            'user_info' => $userInfo,
            'phones' => $phones,
            'images' => $images,
            'countNews' => $countNews,
            'countSubscribers' => $countSubscribers,
            'countSubscriptions' => $countSubscriptions,
        ];
    }
}
