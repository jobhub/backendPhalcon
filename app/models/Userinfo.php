<?php

namespace App\Models;

use App\Libs\SupportClass;
use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Alpha as AlphaValidator;

class Userinfo extends \Phalcon\Mvc\Model
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

    /**
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $city_id;

    protected $email;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=true)
     */
    protected $nickname;


    const publicColumns = ['user_id', 'first_name', 'last_name', 'patronymic',
        'birthday', 'male', 'city_id', 'about', 'status', 'rating_executor', 'rating_client',
        'path_to_photo', 'last_time', 'nickname'];

    const publicColumnsInStr = 'user_id, first_name, last_name, patronymic,
        birthday, male, city_id, about, status, rating_executor, rating_client, 
        path_to_photo, last_time, nickname';

    const shortColumns = ['user_id', 'first_name', 'last_name', 'path_to_photo'];

    const shortColumnsInStr = 'user_id, first_name, last_name, path_to_photo';

    const DEFAULT_RESULT_PER_PAGE = 10;

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->city_id;
    }

    /**
     * @param int $city_id
     */
    public function setCityId($city_id)
    {
        $this->city_id = $city_id;
    }



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
        if ($birthday === "")
            $birthday = null;
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
        /*if ($male == 'мужской' || $male == 'Мужской')
            $this->male = 1;
        else if ($male == 'женский' || $male == 'Женский')
            $this->male = 0;
        else*/
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

        if ($this->getPathToPhoto() != null)
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

        if ($this->getEmail() != null)
            $validator->add(
                'email',
                new EmailValidator(
                    [
                        'model' => $this,
                        'message' => 'Введите, пожалуйста, правильный адрес',
                    ]
                )
            );

        $validator->add(
            'nickname',
            new Callback(
                [
                    "message" => "Такой nickname уже используется",
                    "callback" => function ($user) {
                        $userinfos = Userinfo::findByNickname($user->getNickname());

                        if(count($userinfos)>1)
                            return false;

                        if(count($userinfos) == 0)
                            return true;

                        if($userinfos[0]->getUserId() == $user->getUserId())
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            "nickname",
            new AlphaValidator(
                [
                    "message" => ":field must contain only letters",
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
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
        $this->belongsTo('city_id', 'App\Models\Cities', 'city_id', ['alias' => 'Cities']);
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

    public static function findUserInfoById(int $userId, array $columns = null)
    {
        if ($columns == null)
            return self::findFirst(['user_id = :userId:',
                'bind' => ['userId' => $userId]]);
        else {
            return self::findFirst(['columns' => $columns, 'user_id = :userId:',
                'bind' => ['userId' => $userId]]);
        }
    }

    public static function handleUserInfo(Userinfo $userInfo, Accounts $accountReceiver = null)
    {
        $phones = PhonesUsers::getUserPhones($userInfo->getUserId());
        $images = ImagesUsers::findImages('App\Models\ImagesUsers', $userInfo->getUserId());

        $account = Accounts::findForUserDefaultAccount($userInfo->getUserId());

        $handledUserInfo = SupportClass::getCertainColumnsFromArray($userInfo->toArray(),self::publicColumns);
        unset($handledUserInfo['city_id']);
        $handledUserInfo['city'] = ['city' => $userInfo->cities->getCity(), 'city_id' => $userInfo->getCityId()];

        $data = [
            'user_info' => $handledUserInfo,
            'phones' => $phones,
            'images' => $images,
        ];

        if (!$account)
            return $data;

        $data = Accounts::addInformationForCabinet($account, $data, $accountReceiver);

        return $data;
    }

    public static function findUsersByQueryWithFilters($query,
                                                       $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE,
                                                       $ageMin = null, $ageMax = null,
                                                       $male = null, $hasPhoto = null)
    {
        $db = DI::getDefault()->getDb();

        $query = str_replace('!', '', $query);
        $query = str_replace('|', '', $query);
        $query = str_replace('&', '', $query);
        $ress = explode(' ', $query);
        $res2 = [];
        foreach ($ress as $res) {
            if (trim($res) != "")
                $res2[] = trim($res);
        }

        $str = implode(' ', $res2);

        $sqlQuery = "select user_id, email, phone,
            first_name,last_name, patronymic,
            male, birthday,path_to_photo,status from get_users_like(:str) ";

        $params = [
            'str' => $str,
        ];

        $whereExists = false;

        if ($ageMin != null && $ageMin != false) {
            $dateMin = date('Y-m-d H:i:sO', mktime(date('H'), date('i'), date('s'),
                date('m'), date('d'), date('Y') - $ageMin));
            if ($whereExists)
                $sqlQuery .= " and birthday <= '" . $dateMin . '\'::date';
            else {
                $whereExists = true;
                $sqlQuery .= "where birthday <= '" . $dateMin . '\'::date';
            }
            //$params['dateMin'] = $dateMin;
        }

        if ($ageMax != null && $ageMax != false) {
            $dateMax = date('Y-m-d H:i:sO', mktime(date('H'), date('i'), date('s'),
                date('m'), date('d'), date('Y') - $ageMax));
            if ($whereExists)
                $sqlQuery .= " and birthday >= '" . $dateMax . '\'::date';
            else {
                $whereExists = true;
                $sqlQuery .= "where birthday >= '" . $dateMax . '\'::date';
            }

            /*$params['dateMax'] = $dateMax;*/
        }

        if ($male != null && $male != false || is_integer($male)) {
            if ($whereExists)
                $sqlQuery .= " and male = :male";
            else {
                $whereExists = true;
                $sqlQuery .= " where male = :male";
            }
            $params['male'] = $male;
        }

        if (!is_null($hasPhoto) || is_bool($hasPhoto)) {
            if ($whereExists) {
                if ($hasPhoto)
                    $sqlQuery .= " and not (path_to_photo is null)";
                else {
                    $sqlQuery .= " and (path_to_photo is null)";
                }
            } else {
                $whereExists = true;
                if ($hasPhoto)
                    $sqlQuery .= " where not (path_to_photo is null)";
                else {
                    $sqlQuery .= " where (path_to_photo is null)";
                }
            }
        }
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        $sqlQuery .= " LIMIT :limit";
        $sqlQuery .= " OFFSET :offset";

        $params['limit'] = $page_size;
        $params['offset'] = $offset;

        $query = $db->prepare($sqlQuery);

        $query->execute($params);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
//$str = var_export($result, true);
        return $result;
    }
}
