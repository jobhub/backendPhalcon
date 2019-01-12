<?php

namespace App\Models;

class Channels extends NotDeletedModel
{

    const PUBLIC_COLUMNS = ['id', 'name', 'avatar_path', 'status', 'create_at', 'creator_id'];

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
     * @var string
     */
    protected $avatar_path;

    /**
     *
     * @var string
     */
    protected $status;

    /**
     *
     * @var string
     */
    protected $create_at;

    /**
     *
     * @var integer
     */
    protected $creator_id;


    /**
     *
     * @var integer
     */
    protected $canonical_name;

    /**
     *
     * @var boolean
     */
    protected $is_public;

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
        $this->canonical_name = strtolower($name);

        return $this;
    }

    /**
     * Method to set the value of field avatar_path
     *
     * @param string $avatar_path
     * @return $this
     */
    public function setAvatarPath($avatar_path)
    {
        $this->avatar_path = $avatar_path;

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
     * Returns the value of field avatar_path
     *
     * @return string
     */
    public function getAvatarPath()
    {
        return $this->avatar_path;
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
     * Returns the value of field creator_id
     *
     * @return integer
     */
    public function getCreatorId()
    {
        return $this->creator_id;
    }


    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getCanonicalName(): int
    {
        return $this->canonical_name;
    }

    /**
     * @param int $canonical_name
     */
    public function setCanonicalName(int $canonical_name)
    {
        $this->canonical_name = $canonical_name;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
     * @param bool $is_public
     */
    public function setIsPublic(bool $is_public)
    {
        $this->is_public = $is_public;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("channels");
        $this->hasMany('id', 'App\Models\ChannelMessages', 'channel_id', ['alias' => 'ChannelMessages']);
        $this->hasMany('id', 'App\Models\ChannelUsersSubscriber', 'channel_id', ['alias' => 'ChannelUsersSubscriber']);
        $this->belongsTo('creator_id', 'App\Models\Users', 'user_id', ['alias' => 'User']);
    }



    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'channels';
    }

    public function getSequenceName() {
        return "\"channels_id_seq\"";
    }

}
