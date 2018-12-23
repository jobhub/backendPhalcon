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
    protected $imageid;

    /**
     * Method to set the value of field imageid
     *
     * @param integer $imageid
     * @return $this
     */
    public function setImageId($imageid)
    {
        $this->imageid = $imageid;

        return $this;
    }

    /**
     * Returns the value of field imageid
     *
     * @return integer
     */
    public function getImageId()
    {
        return $this->imageid;
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
            'replyid',
            new Callback(
                [
                    "message" => "Попытка оставить комментарий на несуществующий комментарий была неуспешна",
                    "callback" => function ($comment) {
                        $replycomment = CommentsImagesUsers::findFirst(['commentid = :commentid:',
                            'bind' =>['commentid'=>$comment->getReplyId()]
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
        $this->belongsTo('imageid', '\Imagesusers', 'imageid', ['alias' => 'Imagesusers']);
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
        $comments = CommentsImagesUsers::find(['imageid = :imageId:','bind' =>['imageId'=> $imageId],
            'order' => 'commentdate DESC'],false);

        $comments_arr =  CommentsModel::handleComments($comments);
        for($i = 0; $i < count($comments_arr);$i++){
            $comments_arr[$i]['likes'] = count(LikesCommentsImagesUsers::findByCommentId($comments_arr[$i]['commentid']));
        }
        return $comments_arr;
    }
}
