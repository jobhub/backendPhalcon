<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class CommentsNews extends CommentsModel
{
    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $newsid;

    /**
     * Method to set the value of field newsid
     *
     * @param integer $newsid
     * @return $this
     */
    public function setNewsid($newsid)
    {
        $this->newsid = $newsid;

        return $this;
    }

    /**
     * Returns the value of field newsid
     *
     * @return integer
     */
    public function getNewsid()
    {
        return $this->newsid;
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
                            $replycomment = CommentsNews::findFirst(['commentid = :commentid:',
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
        $this->setSchema("public");
        $this->setSource("comments_news");
        $this->hasMany('commentid', 'LikesCommentsNews', 'commentid', ['alias' => 'LikesCommentsNews']);
        $this->belongsTo('newsid', '\News', 'newsid', ['alias' => 'News']);
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
        $comments = CommentsNews::find(['newsid = :newsId:','bind' =>['newsId'=> $newsId],
            'order' => 'commentdate DESC'],false);

        $comments_arr =  CommentsModel::handleComments($comments);
        for($i = 0; $i < count($comments_arr);$i++){
            $comments_arr[$i]['likes'] = count(LikesCommentsNews::findByCommentId($comments_arr[$i]['commentid']));
        }
        return $comments_arr;
    }

}
