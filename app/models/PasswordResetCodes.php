<?php
namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class PasswordResetCodes extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $reset_code;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $deactivate_code;

    /**
     *
     * @var string
     * @Column(type="string", length=40, nullable=true)
     */
    protected $reset_code_phone;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $time;

    /**
     * Минимальное время, которое должно пройти перед повторной отправкой.
     * В asszсекундах.
     */
    const RESEND_TIME = 300;

    /**
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Method to set the value of field reset_code
     *
     * @param string $reset_code
     * @return $this
     */
    public function setResetCode($reset_code)
    {
        $this->reset_code = $reset_code;

        return $this;
    }

    /**
     * Method to set the value of field deactivate_code
     *
     * @param string $deactivate_code
     * @return $this
     */
    public function setDeactivateCode($deactivate_code)
    {
        $this->deactivate_code = $deactivate_code;

        return $this;
    }

    /**
     * Method to set the value of field reset_code_phone
     *
     * @param string $reset_code_phone
     * @return $this
     */
    public function setResetCodePhone($reset_code_phone)
    {
        $this->reset_code_phone = $reset_code_phone;

        return $this;
    }

    public function generateResetCodePhone($userId)
    {
        $hash = hash('sha256',$userId . time() . rand());
        $this->reset_code_phone = substr($hash,5,10);

        return $this;
    }

    public function generateResetCode($userId)
    {
        $hash = hash('sha256',$userId . time() . rand());
        $this->reset_code = $hash;

        return $this;
    }

    public function generateDeactivateResetCode($userId)
    {
        $hash = hash('sha256',$userId . time() . rand(). '-no');
        $this->deactivate_code = $hash;

        return $this;
    }

    /**
     * Method to set the value of field time
     *
     * @param string $time
     * @return $this
     */
    public function setTime($time = null)
    {
        if($time == null){
            $this->time = date('Y-m-d H:i:s');
        } else
            $this->time = $time;

        return $this;
    }

    /**
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Returns the value of field reset_code
     *
     * @return string
     */
    public function getResetCode()
    {
        return $this->reset_code;
    }

    /**
     * Returns the value of field deactivate_code
     *
     * @return string
     */
    public function getDeactivateCode()
    {
        return $this->deactivate_code;
    }

    /**
     * Returns the value of field reset_code_phone
     *
     * @return string
     */
    public function getResetCodePhone()
    {
        return $this->reset_code_phone;
    }

    /**
     * Returns the value of field time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
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
            'userid',
            new Callback(
                [
                    "message" => "Пользователь не существует",
                    "callback" => function ($resetcode
                    ) {
                        $user = Users::findFirst(['userid = :userId:', 'bind' => ['userId' => $resetcode
                            ->getUserId()]],
                            false);
                        if (!$user)
                            return false;
                        return true;
                    }
                ]
            )
        );

        /*$validator->add(
            'userid',
            new Callback(
                [
                    "message" => "Код для сброса пароля уже создан для этого пользователя",
                    "callback" => function ($resetcode
                    ) {
                        $resetCodeExists = PasswordResetCodes::findFirstByUserid($resetcode
                            ->getUserId());
                        if($resetCodeExists)
                            return false;
                        return true;
                    }
                ]
            )
        );*/

        $validator->add(
            'reset_code',
            new Callback(
                [
                    "message" => "Должен быть сгенерирован либо код для почты, либо код для sms",
                    "callback" => function ($resetcode
                    ) {
                        if (($resetcode->getResetCode() == null || trim($resetcode->getResetCode()) == "")
                            && ($resetcode->getResetCodePhone() == null || trim($resetcode->getResetCodePhone()) == "")) {
                            return false;
                        }
                        return true;
                    }
                ]
            )
        );

        $validator->add(
            'time',
            new PresenceOf(
                [
                    "message" => "Не указано время выдачи кода",
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
        $this->setSchema("public");
        $this->setSource("password_reset_codes");
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'password_reset_codes';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PasswordResetCodes[]|PasswordResetCodes|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PasswordResetCodes|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
