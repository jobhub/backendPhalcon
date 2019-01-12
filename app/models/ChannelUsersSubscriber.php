<?php

namespace App\Models;

class ChannelUsersSubscriber extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $subscribe_at;

    /**
     *
     * @var integer
     */
    protected $user_id;

    /**
     *
     * @var integer
     */
    protected $channel_id;

    /**
     *
     * @var boolean
     */
    protected $is_admin;

    /**
     * Method to set the value of field id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to set the value of field subscribe_at
     *
     * @param string $subscribe_at
     * @return $this
     */
    public function setSubscribeAt($subscribe_at)
    {
        $this->subscribe_at = $subscribe_at;

        return $this;
    }

    /**
     * Method to set the value of field user_id
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
     * Method to set the value of field channel_id
     *
     * @param integer $channel_id
     * @return $this
     */
    public function setChannelId($channel_id)
    {
        $this->channel_id = $channel_id;

        return $this;
    }

    /**
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field subscribe_at
     *
     * @return string
     */
    public function getSubscribeAt()
    {
        return $this->subscribe_at;
    }

    /**
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field channel_id
     *
     * @return integer
     */
    public function getChannelId()
    {
        return $this->channel_id;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * @param bool $is_admin
     */
    public function setIsAdmin(bool $is_admin)
    {
        $this->is_admin = $is_admin;
    }



    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("channel_users_subscriber");
        $this->belongsTo('channel_id', 'App\Models\Channels', 'id', ['alias' => 'Channels']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'channel_users_subscriber';
    }

    public function getSequenceName() {
        return "\"channel_users_subscriber_id_seq\"";
    }
    
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ChannelUsersSubscriber[]|ChannelUsersSubscriber|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ChannelUsersSubscriber|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
