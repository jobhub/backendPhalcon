<?php

class LikesCommentsImagesUsers extends SubjectsModel
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $commentid;


    /**
     * Method to set the value of field commentid
     *
     * @param integer $commentid
     * @return $this
     */
    public function setCommentid($commentid)
    {
        $this->commentid = $commentid;

        return $this;
    }

    /**
     * Returns the value of field commentid
     *
     * @return integer
     */
    public function getCommentid()
    {
        return $this->commentid;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("likes_comments_imagesusers");
        $this->belongsTo('commentid', '\CommentsImagesusers', 'commentid', ['alias' => 'CommentsImagesusers']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'likes_comments_imagesusers';
    }
}
