<?php

namespace App\Models;

class RastreniyaResponses extends LikeDislikeModel
{

    const PUBLIC_COLUMNS = ['content', 'id', 'create_at', 'has_attached','account_id'];
    const PUBLIC_INFO = ['content', 'id', 'create_at', 'has_attached','account_id'];
    const DEFAULT_RESULT_PER_PAGE = 12;
    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var string
     */
    protected $create_at;

    /**
     *
     * @var string
     */
    protected $has_attached;


    /**
     *
     * @var integer
     */
    protected $user_id;

    /**
     *
     * @var integer
     */
    protected $rastreniya_id;

    /**
     *
     * @var integer
     */
    protected $parent_id;

    /**
     *
     * @var integer
     */
    protected $account_id;

    /**
     * Method to set the value of field id
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * Method to set the value of field has_attached
     *
     * @param string $has_attached
     * @return $this
     */
    public function setHasAttached($has_attached)
    {
        $this->has_attached = $has_attached;

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
     * Method to set the value of field rastreniya_id
     *
     * @param integer $rastreniya_id
     * @return $this
     */
    public function setRastreniyaId($rastreniya_id)
    {
        $this->rastreniya_id = $rastreniya_id;

        return $this;
    }

    /**
     * Returns the value of field id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * Returns the value of field create_at
     *
     * @return string
     */
    public function getCreateAt()
    {
        return $this->create_at;
    }

    /**
     * Returns the value of field has_attached
     *
     * @return string
     */
    public function getHasAttached()
    {
        return $this->has_attached;
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
     * Returns the value of field rastreniya_id
     *
     * @return integer
     */
    public function getRastreniyaId()
    {
        return $this->rastreniya_id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId(int $parent_id)
    {
        $this->parent_id = $parent_id;
    }


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("rastreniya_responses");
        $this->belongsTo('rastreniya_id', 'App\Models\Rastreniya', 'id', ['alias' => 'Rastreniya']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'User']);
        $this->belongsTo('account_id', 'App\Models\Account', 'id', ['alias' => 'Account']);
        $this->belongsTo('parent_id', 'App\Models\RastreniyaResponses', 'id', ['alias' => 'Parent']);
        $this->hasMany('id', 'App\Models\RastreniyaResponses', 'parent_id', ['alias' => 'Childs']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'rastreniya_responses';
    }

    /**
     * @return int
     */
    public function getAccountId(): int
    {
        return $this->account_id;
    }

    /**
     * @param int $account_id
     */
    public function setAccountId(int $account_id)
    {
        $this->account_id = $account_id;
    }



    public function getSequenceName() {
        return "\"rastreniya_responses_id_seq\"";
    }



    /**
     * Build an array with only public data
     *
     * @return array
     */
    public function getPublicInfo(){
        $toRet = [];
        foreach (self::PUBLIC_INFO as $info)
            $toRet[$info] = $this->$info;
        return $toRet;
    }
}
