<?php

namespace App\Models;

class Rastreniya extends LikeDislikeModel
{

    const PUBLIC_INFO = ['id', 'create_at', 'is_incognito', 'content', 'has_attached_files'];

    const DEFAULT_RESULT_PER_PAGE = 12;
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
     * @var string
     */
    protected $is_incognito;

    /**
     *
     * @var integer
     */
    protected $user_id;


    /**
     *
     * @var integer
     */
    protected $account_id;


    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var string
     */
    protected $has_attached_files;

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
     * Method to set the value of field has_attached_files
     *
     * @param string $has_attached_files
     * @return $this
     */
    public function setHasAttachedFiles($has_attached_files)
    {
        $this->has_attached_files = $has_attached_files;

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
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
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
     * Returns the value of field has_attached_files
     *
     * @return string
     */
    public function getHasAttachedFiles()
    {
        return $this->has_attached_files;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("rastreniya");
        $this->hasMany('id', 'App\Models\RastreniyaResponses', 'rastreniya_id', ['alias' => 'RastreniyaResponses']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'User']);
        $this->belongsTo('account_id', 'App\Models\Accounts', 'id', ['alias' => 'Account']);
    }

    /**
     * @return bool
     */
    public function isIncognito(): bool
    {
        return $this->is_incognito;
    }

    /**
     * @param bool $is_incognito
     */
    public function setIsIncognito(bool $is_incognito)
    {
        $this->is_incognito = $is_incognito;
    }


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'rastreniya';
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

    /**
     * @return int
     */
    public function getAccountId()
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

}
