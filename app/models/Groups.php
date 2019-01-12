<?php

namespace App\Models;

class Groups extends \Phalcon\Mvc\Model
{

    const PUBLIC_COLUMNS = ['id', 'name', 'create_at', 'creator_id'];

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var integer
     */
    protected $chat_hist_id;

    /**
     *
     * @var integer
     */
    protected $creator_id;

    /**
     *
     * @var string
     */
    protected $create_at;

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
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field chat_hist_id
     *
     * @param integer $chat_hist_id
     * @return $this
     */
    public function setChatHistId($chat_hist_id)
    {
        $this->chat_hist_id = $chat_hist_id;

        return $this;
    }

    /**
     * Method to set the value of field creator_id
     *
     * @param integer $creator_id
     * @return $this
     */
    public function setCreatorId($creator_id)
    {
        $this->creator_id = $creator_id;

        return $this;
    }

    /**
     * Method to set the value of field create_at
     *
     * @param string $create_at
     * @return $this
     */
    public function setCreateAt($create_at)
    {
        $this->create_at = $create_at;

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
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field chat_hist_id
     *
     * @return integer
     */
    public function getChatHistId()
    {
        return $this->chat_hist_id;
    }

    /**
     * Returns the value of field creator_id
     *
     * @return integer
     */
    public function getCreatorId()
    {
        return $this->creator_id;
    }

    /**
     * Returns the value of field create_at
     *
     * @return string
     */
    public function getCreateAt()
    {
        return $this->create_at;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("groups");
        $this->hasMany('id', 'App\Models\GroupsAccounts', 'group_id', ['alias' => 'GroupsAccounts']);
        $this->hasMany('id', 'App\Models\UserChatGroups', 'group_id', ['alias' => 'UserChatGroups']);
        $this->belongsTo('chat_hist_id', 'App\Models\ChatHistory', 'id', ['alias' => 'Chathistory']);
        $this->belongsTo('creator_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'groups';
    }


    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Groups[]|Groups|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Groups|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
