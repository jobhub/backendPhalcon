<?php

namespace App\Models;

class Settings extends \Phalcon\Mvc\Model
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
     * @var integer
     * @Column(type="string", nullable=false)
     */
    protected $notification_email;

    /**
     *
     * @var integer
     * @Column(type=""string", nullable=false)
     */
    protected $notification_sms;

    /**
     *
     * @var integer
     * @Column(type="string", nullable=false)
     */
    protected $notification_push;

    /**
     *
     * @var integer
     * @Column(type=""string", nullable=false)
     */
    protected $show_companies;

    /**
     * @return int
     */
    public function getShowCompanies()
    {
        return $this->show_companies;
    }

    /**
     * @param int $show_companies
     */
    public function setShowCompanies($show_companies)
    {
        $this->show_companies = $show_companies;
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
     * Method to set the value of field notificationEmail
     *
     * @param integer $notification_email
     * @return $this
     */
    public function setNotificationEmail($notification_email)
    {
        $this->notification_email = $notification_email;

        return $this;
    }

    /**
     * Method to set the value of field notificationSms
     *
     * @param integer $notification_sms
     * @return $this
     */
    public function setNotificationSms($notification_sms)
    {
        $this->notification_sms = $notification_sms;

        return $this;
    }

    /**
     * Method to set the value of field notificationPush
     *
     * @param integer $notification_push
     * @return $this
     */
    public function setNotificationPush($notification_push)
    {
        $this->notification_push = $notification_push;

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
     * Returns the value of field notificationEmail
     *
     * @return integer
     */
    public function getNotificationEmail()
    {
        return $this->notification_email;
    }

    /**
     * Returns the value of field notificationSms
     *
     * @return integer
     */
    public function getNotificationSms()
    {
        return $this->notification_sms;
    }

    /**
     * Returns the value of field notificationPush
     *
     * @return integer
     */
    public function getNotificationPush()
    {
        return $this->notification_push;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("settings");
        $this->hasOne('user_id', 'App\Models\User', 'user_id', ['alias' => 'User']);
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
