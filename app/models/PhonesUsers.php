<?php

namespace App\Models;
use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class PhonesUsers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $phone_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $user_id;

    /**
     * Method to set the value of field phoneid
     *
     * @param integer $phone_id
     * @return $this
     */
    public function setPhoneId($phone_id)
    {
        $this->phone_id = $phone_id;

        return $this;
    }

    /**
     * Method to set the value of field userid
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
     * Returns the value of field phoneid
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phone_id;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("phones_users");
        $this->belongsTo('phone_id', 'App\Models\Phones', 'phone_id', ['alias' => 'Phones']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
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
            'phone_id',
            new Callback(
                [
                    "message" => "Телефон не был создан",
                    "callback" => function($phoneUser) {
                        $phone = Phones::findFirstByPhoneId($phoneUser->getPhoneId());

                        if($phone)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'user_id',
            new Callback(
                [
                    "message" => "Такой пользователь не существует",
                    "callback" => function($phoneUser) {
                        $phone = Users::findFirstByUserId($phoneUser->getUserId());

                        if($phone)
                            return true;
                        return false;
                    }
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'phones_users';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesUsers[]|PhonesUsers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesUsers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($userId, $phoneId)
    {
        return PhonesUsers::findFirst(["user_id = :userId: AND phone_id = :phoneId:",
            'bind' =>
                [
                    'userId' => $userId,
                    'phoneId' => $phoneId
                ]]);
    }

    public static function getUserPhones($userId)
    {
        $db = DI::getDefault()->getDb();

        $query = $db->prepare("SELECT p.phone, p.phone_id FROM phones_users p_u INNER JOIN phones p ON 
            (p_u.phone_id = p.phone_id) where p_u.user_id = :userId"
        );

        $query->execute([
            'userId' => $userId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

}
