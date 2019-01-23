<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class CommentsImagesUsers extends CommentsModel
{

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $image_id;

    /**
     * Method to set the value of field imageid
     *
     * @param integer $imageid
     * @return $this
     */
    public function setImageId($imageid)
    {
        $this->image_id = $imageid;

        return $this;
    }

    /**
     * Returns the value of field imageid
     *
     * @return integer
     */
    public function getImageId()
    {
        return $this->image_id;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if($this->getReplyId()!=null)
        $validator->add(
            'reply_id',
            new Callback(
                [
                    "message" => "Попытка оставить комментарий на несуществующий комментарий была неуспешна",
                    "callback" => function ($comment) {
                        $replycomment = CommentsImagesUsers::findFirst(['comment_id = :commentId:',
                            'bind' =>['commentId'=>$comment->getReplyId()]
                        ], false);

                        if ($replycomment)
                            return true;
                        return false;
                    }
                ]
            )
        );


        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSource("comments_imagesusers");
        $this->belongsTo('object_id', 'App\Models\ImagesUsers', 'image_id', ['alias' => 'ImagesUsers']);
    }

    public function getSequenceName()
    {
        return "comments_imagesusers_commentid_seq";
    }


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'comments_imagesusers';
    }

    public static function getComments($imageId){
        $comments = CommentsImagesUsers::find(['image_id = :imageId:','bind' =>['imageId'=> $imageId],
            'order' => 'comment_date DESC'],false);

        $comments_arr =  CommentsModel::handleComments($comments->toArray());
        for($i = 0; $i < count($comments_arr);$i++){
            $comments_arr[$i]['likes'] = count(LikesCommentsImagesUsers::findByCommentId($comments_arr[$i]['comment_id']));
        }
        return $comments_arr;
    }
}
