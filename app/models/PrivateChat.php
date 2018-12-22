<?php

namespace App\Models;

class PrivateChat extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var integer
     */
    protected $user1;

    /**
     *
     * @var integer
     */
    protected $user2;

    /**
     *
     * @var integer
     */
    protected $chat_hist_id;

    /**
     *
     * @var integer
     */
    protected $subject_type_user1;

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
     * Method to set the value of field user1
     *
     * @param integer $user1
     * @return $this
     */
    public function setUser1($user1)
    {
        $this->user1 = $user1;

        return $this;
    }

    /**
     * Method to set the value of field user2
     *
     * @param integer $user2
     * @return $this
     */
    public function setUser2($user2)
    {
        $this->user2 = $user2;

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
     * Method to set the value of field subject_type_user1
     *
     * @param integer $subject_type_user1
     * @return $this
     */
    public function setSubjectTypeUser1($subject_type_user1)
    {
        $this->subject_type_user1 = $subject_type_user1;

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
     * Returns the value of field user1
     *
     * @return integer
     */
    public function getUser1()
    {
        return $this->user1;
    }

    /**
     * Returns the value of field user2
     *
     * @return integer
     */
    public function getUser2()
    {
        return $this->user2;
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
     * Returns the value of field subject_type_user1
     *
     * @return integer
     */
    public function getSubjectTypeUser1()
    {
        return $this->subject_type_user1;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("privateChat");
        $this->belongsTo('chat_hist_id', 'App\Models\ChatHistory', 'id', ['alias' => 'Chathistory']);
        $this->belongsTo('user1', 'App\Models\User', 'id', ['alias' => 'User']);
        $this->belongsTo('user2', 'App\Models\User', 'id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'privateChat';
    }
    
    public function getSequenceName()
    {
        return "\"PrivateChat_id_seq\"";
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PrivateChat[]|PrivateChat|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PrivateChat|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
