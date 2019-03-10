<?php

namespace App\Models;

class PrivateChat extends HiddenSpamModel
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
    protected $user_id;

    /**
     *
     * @var integer
     */
    protected $related_user_id;

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
     * Method to set the value of field related_user_id
     *
     * @param integer $related_user_id
     * @return $this
     */
    public function setRelatedUserId($related_user_id)
    {
        $this->related_user_id = $related_user_id;

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
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field related_user_id
     *
     * @return integer
     */
    public function getRelatedUserId()
    {
        return $this->related_user_id;
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
     * @return string
     */
    public function getCreateAt(): string
    {
        return $this->create_at;
    }

    /**
     * @param string $create_at
     */
    public function setCreateAt(string $create_at)
    {
        $this->create_at = $create_at;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("privateChat");
        $this->belongsTo('chat_hist_id', 'App\Models\ChatHistory', 'id', ['alias' => 'Chathistory']);
        $this->belongsTo('related_user_id', 'App\Models\Users', 'user_id', ['alias' => 'relatedUser']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'User']);
    }

    public static function newFind($user_id){
        $data = PrivateChat::query()
            ->where('user_id = :user:')
            ->andWhere('deleted != true AND is_hidden != true  AND is_spam != true')
            ->bind(["user" => $user_id])
            ->innerJoin('ChatHistory')
            ->inWhere("id", [1, 2, 3]);
            //->orderBy("Chathistory.last_modification_date")
           // ->limit(1)
            //->execute();
        var_dump($data);
        return $data;
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
}
