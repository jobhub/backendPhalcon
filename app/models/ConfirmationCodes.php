<?php

namespace App\Models;

class ConfirmationCodes extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $code_id;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    protected $confirm_code_email;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    protected $deactivate_code;

    /**
     *
     * @var string
     * @Column(type="string", length=12, nullable=true)
     */
    protected $confirm_code_phone;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $type;

    protected $time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $user_id;

    /**
     * Минимальное время, которое должно пройти перед повторной отправкой.
     * В секундах.
     */
    const RESEND_TIME = 300;

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Method to set the value of field code_id
     *
     * @param integer $code_id
     * @return $this
     */
    public function setCodeId($code_id)
    {
        $this->code_id = $code_id;

        return $this;
    }

    /**
     * Method to set the value of field confirm_code_email
     *
     * @param string $confirm_code_email
     * @return $this
     */
    public function setConfirmCodeEmail($confirm_code_email)
    {
        $this->confirm_code_email = $confirm_code_email;

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
     * Method to set the value of field confirm_code_phone
     *
     * @param string $confirm_code_phone
     * @return $this
     */
    public function setConfirmCodePhone($confirm_code_phone)
    {
        $this->confirm_code_phone = $confirm_code_phone;

        return $this;
    }

    /**
     * Method to set the value of field type
     *
     * @param integer $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the value of field code_id
     *
     * @return integer
     */
    public function getCodeId()
    {
        return $this->code_id;
    }

    /**
     * Returns the value of field confirm_code_email
     *
     * @return string
     */
    public function getConfirmCodeEmail()
    {
        return $this->confirm_code_email;
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
     * Returns the value of field confirm_code_phone
     *
     * @return string
     */
    public function getConfirmCodePhone()
    {
        return $this->confirm_code_phone;
    }

    /**
     * Returns the value of field type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("confirmation_codes");
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'confirmation_codes';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ConfirmationCodes[]|ConfirmationCodes|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ConfirmationCodes|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
