<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

use App\Libs\SupportClass;

use Phalcon\DI\FactoryDefault as DI;

class UserLocation extends \Phalcon\Mvc\Model
{

    const DEFAULT_RESULT_PER_PAGE = 10;
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
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $marker_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $last_time;

    /**
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserId($userid)
    {
        $this->user_id = $userid;

        return $this;
    }

    /**
     * Method to set the value of field lasttime
     *
     * @param string $lasttime
     * @return $this
     */
    public function setLastTime($lasttime)
    {
        $this->last_time = $lasttime;

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
     * Returns the value of field lasttime
     *
     * @return string
     */
    public function getLastTime()
    {
        return $this->last_time;
    }

    /**
     * @return string
     */
    public function getMarkerId(): string
    {
        return $this->marker_id;
    }

    /**
     * @param string $marker_id
     */
    public function setMarkerId(string $marker_id)
    {
        $this->marker_id = $marker_id;
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
            'user_id',
            new Callback(
                [
                    "message" => "Пользователь не существует или был удален",
                    "callback" => function ($userlocation) {
                        $user = Users::findFirstByUserId($userlocation->getUserId());
                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        /*$validator->add(
            'lasttime',
            new Callback(
                [
                    "message" => "Время актуальности местоположения долно быть раньше текущего. Никуда не денешься, элементарная логика.",
                    "callback" => function ($userlocation) {
                        if (strtotime($userlocation->getLastTime()) <= time())
                            return true;
                        return false;
                    }
                ]
            )
        );*/

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("user_location");
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
        $this->belongsTo('marker_id', 'App\Models\Markers', 'marker_id', ['alias' => 'Markers']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user_location';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserLocation[]|UserLocation|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserLocation|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /*public static function findUsersByQuery($query, $longitudeRH, $latitudeRH,
                                            $longitudeLB, $latitudeLB)
    {

        $db = Phalcon\DI::getDefault()->getDb();

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

        $query = $db->prepare("select user_id, email, phone,
    first_name,last_name, patronymic, longitude, latitude, last_time,
    male, birthday,path_to_photo,status from get_users_for_search_like(:str,:longituderh,
            :latituderh, :longitudelb, :latitudelb) 
            where last_time > :last_time
            LIMIT 50");

        $query->execute([
            'str' => $str,
            'longituderh' => $longitudeRH,
            'latituderh' => $latitudeRH,
            'longitudelb' => $longitudeLB,
            'latitudelb' => $latitudeLB,
            'last_time' => date('Y-m-d H:i:s', time() + -3600),
        ]);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $str = var_export($result, true);

        SupportClass::writeMessageInLogFile('Результат поиска по юзерам:');
        SupportClass::writeMessageInLogFile($str);
        return $result;
    }*/

    public static function findUsersByQueryWithFilters($query, $longitudeRH, $latitudeRH,
                                                       $longitudeLB, $latitudeLB, $ageMin = null, $ageMax = null,
                                                       $male = null, $hasPhoto = null,
                                                       $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

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
    first_name,last_name, patronymic, longitude, latitude, last_time,
    male, birthday,path_to_photo,status from get_users_for_search_like_2(:str,:longituderh,
            :latituderh, :longitudelb, :latitudelb) 
            where last_time > (:last_time)::date";

        $params = [
            'str' => $str,
            'longituderh' => $longitudeRH,
            'latituderh' => $latitudeRH,
            'longitudelb' => $longitudeLB,
            'latitudelb' => $latitudeLB,
            'last_time' => date('Y-m-d H:i:sO', time() + -3600),
        ];

        $whereExists = true;

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
            $dateMax = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'),
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

        $sqlQuery .= " ORDER BY last_time desc
                    LIMIT :limit 
                    OFFSET :offset";

        $params['limit'] = $page_size;
        $params['offset'] = $offset;

        $query = $db->prepare($sqlQuery);

        $query->execute($params);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);

        return self::handleUsersSearch($result);
    }


    function calculate_age($birthday)
    {
        $birthday_timestamp = strtotime($birthday);
        $age = date('Y') - date('Y', $birthday_timestamp);
        if (date('md', $birthday_timestamp) > date('md')) {
            $age--;
        }
        return $age;
    }

    public static function getAutoComplete($query, $longitudeRH, $latitudeRH,
                                           $longitudeLB, $latitudeLB,
                                           $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

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

        $query = $db->prepare("select user_id, first_name, last_name, patronymic,path_to_photo from 
            get_users_for_search_like_2(:str,:longituderh,
            :latituderh, :longitudelb, :latitudelb) 
            where last_time > (:last_time)::date
            ORDER BY last_time desc
                    LIMIT :limit 
                    OFFSET :offset");

        $query->execute([
            'str' => $str,
            'longituderh' => $longitudeRH,
            'latituderh' => $latitudeRH,
            'longitudelb' => $longitudeLB,
            'latitudelb' => $latitudeLB,
            'last_time' => date('Y-m-d H:i:sO', time() + -3600),
            'limit'=>$page_size,
            'offset'=>$offset
        ]);

        $result = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function getUserinfo($user_id)
    {
        $db = DI::getDefault()->getDb();

        $query = $db->prepare("select users.user_id, users.email, array(SELECT phones.phone FROM public.phones as phones INNER JOIN 
                                  public.phones_users phus ON (phus.phone_id = phones.phone_id) 
                                           WHERE userinfo.user_id = phus.user_id) as phone,
    first_name,last_name, patronymic, longitude, latitude, user_location.last_time,
    male, birthday,path_to_photo,status
            from 
            users 
    INNER JOIN userinfo USING(user_id)
    INNER JOIN user_location USING(user_id)
    INNER JOIN markers USING(marker_id)
    LEFT JOIN phones USING (phone_id)
    where user_id =:user_id
            and user_location.last_time > :last_time");

        $query->execute([
            'user_id' => $user_id,
            'last_time' => date('Y-m-d H:i:sO', time() + -3600),
        ]);

        return self::handleUsersSearch($query->fetchAll(\PDO::FETCH_ASSOC));
    }

    public static function handleUserLocations(array $locations)
    {
        $handledLocations = [];
        foreach ($locations as $location) {
            $handledLocations[] = self::handleUserLocation($location);
        }

        return $handledLocations;
    }

    public static function handleUserLocation(array $location)
    {
        $marker = Markers::findFirstByMarkerId($location['marker_id']);
        $handledLocation = [
            'user_id' => $location['user_id'],
            'last_time' => $location['last_time'],
            'longitude' => $marker->getLongitude(),
            'latitude' => $marker->getLatitude()
        ];

        return $handledLocation;
    }

    public static function handleUsersSearch(array $search_result)
    {
        $handledLocations = [];
        foreach ($search_result as $location) {
            $handledLocations[] = self::handleSearchResult($location);
        }

        return $handledLocations;
    }

    public static function handleSearchResult(array $search_result)
    {
        $handledLocation = [
            'user_id' => $search_result['user_id'],
            'last_time' => $search_result['last_time'],
            'email' => $search_result['email'],
            'phones' => SupportClass::translateInPhpArrFromPostgreArr($search_result['phone']),
            'first_name' => $search_result['first_name'],
            'last_name' => $search_result['last_name'],
            'patronymic' => $search_result['patronymic'],
            'longitude' => $search_result['longitude'],
            'latitude' => $search_result['latitude'],
            'male' => $search_result['male'],
            'birthday' => $search_result['birthday'],
            'path_to_photo' => $search_result['path_to_photo'],
            'status' => $search_result['status'],
        ];

        return $handledLocation;
    }
}
