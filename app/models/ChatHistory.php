<?php

namespace App\Models;

class ChatHistory extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $id;

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
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("chatHistory");
        $this->hasMany('id', 'App\Models\Groups', 'chat_hist_id', ['alias' => 'Group']);
        $this->hasMany('id', 'App\Models\Message', 'chat_hist_id', ['alias' => 'Messages']);
        $this->hasMany('id', 'App\Models\PrivateChat', 'chat_hist_id', ['alias' => 'Privatechat']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'chatHistory';
    }

    public function getSequenceName() {
        return "\"chatHistory_id_seq\"";
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ChatHistory[]|ChatHistory|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ChatHistory|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
