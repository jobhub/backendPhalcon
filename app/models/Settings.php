<?php

class Settings extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $notifictionEmail;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $notificationSms;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $notificationPush;

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
     * Method to set the value of field notifictionEmail
     *
     * @param integer $notifictionEmail
     * @return $this
     */
    public function setNotifictionEmail($notifictionEmail)
    {
        $this->notifictionEmail = $notifictionEmail;

        return $this;
    }

    /**
     * Method to set the value of field notificationSms
     *
     * @param integer $notificationSms
     * @return $this
     */
    public function setNotificationSms($notificationSms)
    {
        $this->notificationSms = $notificationSms;

        return $this;
    }

    /**
     * Method to set the value of field notificationPush
     *
     * @param integer $notificationPush
     * @return $this
     */
    public function setNotificationPush($notificationPush)
    {
        $this->notificationPush = $notificationPush;

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
     * Returns the value of field notifictionEmail
     *
     * @return integer
     */
    public function getNotifictionEmail()
    {
        return $this->notifictionEmail;
    }

    /**
     * Returns the value of field notificationSms
     *
     * @return integer
     */
    public function getNotificationSms()
    {
        return $this->notificationSms;
    }

    /**
     * Returns the value of field notificationPush
     *
     * @return integer
     */
    public function getNotificationPush()
    {
        return $this->notificationPush;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("settings");
        $this->hasOne('userId', '\Userinfo', 'userId', ['alias' => 'Userinfo']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Settings[]|Settings|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Settings|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'settings';
    }

}
