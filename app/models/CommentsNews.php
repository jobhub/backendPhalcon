<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class CommentsNews extends CommentsModel
{
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
                            $replycomment = CommentsNews::findFirst(['comment_id = :commentId:',
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
        $this->setSchema("public");
        $this->setSource("comments_news");
        $this->hasMany('comment_id', 'App\Models\LikesCommentsNews', 'comment_id', ['alias' => 'LikesCommentsNews']);
        $this->belongsTo('object_id', 'App\Models\News', 'news_id', ['alias' => 'News']);
    }

    public function getSequenceName()
    {
        return "comments_news_commentid_seq";
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'comments_news';
    }

    public static function getComments($newsId){
        $comments = CommentsNews::find(['object_id = :newsId:','bind' =>['newsId'=> $newsId],
            'order' => 'comment_date DESC'],false);

        $comments_arr =  CommentsModel::handleComments($comments->toArray());
        /*for($i = 0; $i < count($comments_arr);$i++){
            $comments_arr[$i]['likes'] = count(LikesCommentsNews::findByCommentId($comments_arr[$i]['comment_id']));
        }*/
        return $comments_arr;
    }
}
