<?php

namespace App\Models;

class Message extends \Phalcon\Mvc\Model
{
    const PUBLIC_COLUMNS = ['id', 'create_at', 'chat_hist_id', 'sender_id', 'content', 'received_users', 'readed_users'];

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $create_at;

    /**
     *
     * @var integer
     */
    protected $chat_hist_id;

    /**
     *
     * @var integer
     */
    protected $sender_id;

    /**
     *
     * @var string
     */
    protected $is_readed;

    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var string
     */
    protected $message_type;

    /**
     *
     * @var integer
     */
    protected $subject_type;

    /**
     *
     * @var string
     */
    protected $deleted;

    /**
     *
     * @var string
     */
    protected $received_users;

    /**
     *
     * @var string
     */
    protected $readed_users;

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
     * Method to set the value of field sender_id
     *
     * @param integer $sender_id
     * @return $this
     */
    public function setSenderId($sender_id)
    {
        $this->sender_id = $sender_id;

        return $this;
    }

    /**
     * Method to set the value of field is_readed
     *
     * @param string $is_readed
     * @return $this
     */
    public function setIsReaded($is_readed)
    {
        $this->is_readed = $is_readed;

        return $this;
    }

    /**
     * Method to set the value of field content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Method to set the value of field message_type
     *
     * @param string $message_type
     * @return $this
     */
    public function setMessageType($message_type)
    {
        $this->message_type = $message_type;

        return $this;
    }

    /**
     * Method to set the value of field subject_type
     *
     * @param integer $subject_type
     * @return $this
     */
    public function setSubjectType($subject_type)
    {
        $this->subject_type = $subject_type;

        return $this;
    }

    /**
     * Method to set the value of field deleted
     *
     * @param string $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Method to set the value of field received_users
     *
     * @param string $received_users
     * @return $this
     */
    public function setReceivedUsers($received_users)
    {
        $this->received_users = $received_users;

        return $this;
    }

    /**
     * Method to set the value of field readed_users
     *
     * @param string $readed_users
     * @return $this
     */
    public function setReadedUsers($readed_users)
    {
        $this->readed_users = $readed_users;

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
     * Returns the value of field create_at
     *
     * @return string
     */
    public function getCreateAt()
    {
        return $this->create_at;
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
     * Returns the value of field sender_id
     *
     * @return integer
     */
    public function getSenderId()
    {
        return $this->sender_id;
    }

    /**
     * Returns the value of field is_readed
     *
     * @return string
     */
    public function getIsReaded()
    {
        return $this->is_readed;
    }

    /**
     * Returns the value of field content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Returns the value of field message_type
     *
     * @return string
     */
    public function getMessageType()
    {
        return $this->message_type;
    }

    /**
     * Returns the value of field subject_type
     *
     * @return integer
     */
    public function getSubjectType()
    {
        return $this->subject_type;
    }

    /**
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Returns the value of field received_users
     *
     * @return string
     */
    public function getReceivedUsers()
    {
        return $this->received_users;
    }

    /**
     * Returns the value of field readed_users
     *
     * @return string
     */
    public function getReadedUsers()
    {
        return $this->readed_users;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("message");
        $this->hasMany('id', 'App\Models\File', 'id_message', ['alias' => 'File']);
        $this->belongsTo('chat_hist_id', 'App\Models\ChatHistory', 'id', ['alias' => 'Chathistory']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'message';
    }


    public function getSequenceName() {
        return "\"message_id_seq\"";
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Message[]|Message|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Message|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
