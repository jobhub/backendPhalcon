<?php

namespace App\Models;

class Message extends \Phalcon\Mvc\Model
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
    protected $creationDate;

    /**
     *
     * @var integer
     */
    protected $chat_hist_id;

    /**
     *
     * @var integer
     */
    protected $sender;

    /**
     *
     * @var string
     */
    protected $isReaded;

    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var string
     */
    protected $messageType;

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
     * Method to set the value of field creationDate
     *
     * @param string $creationDate
     * @return $this
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

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
     * Method to set the value of field sender
     *
     * @param integer $sender
     * @return $this
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Method to set the value of field isReaded
     *
     * @param string $isReaded
     * @return $this
     */
    public function setIsReaded($isReaded)
    {
        $this->isReaded = $isReaded;

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
     * Method to set the value of field messageType
     *
     * @param string $messageType
     * @return $this
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;

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
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field creationDate
     *
     * @return string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
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
     * Returns the value of field sender
     *
     * @return integer
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Returns the value of field isReaded
     *
     * @return string
     */
    public function getIsReaded()
    {
        return $this->isReaded;
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
     * Returns the value of field messageType
     *
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
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
