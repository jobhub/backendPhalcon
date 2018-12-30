<?php

namespace App\Models;

/**
 * Class CommentsModel - abstract class for all comment models
 */
abstract class CommentsModel extends AccountWithNotDeleted
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $commentid;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=false)
     */
    protected $commenttext;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $commentdate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $replyid;

    /**
     * Method to set the value of field commentid
     *
     * @param integer $commentid
     * @return $this
     */
    public function setCommentId($commentid)
    {
        $this->commentid = $commentid;

        return $this;
    }

    /**
     * Method to set the value of field commenttext
     *
     * @param string $commenttext
     * @return $this
     */
    public function setCommentText($commenttext)
    {
        $this->commenttext = $commenttext;

        return $this;
    }

    /**
     * Method to set the value of field commentdate
     *
     * @param string $commentdate
     * @return $this
     */
    public function setCommentDate($commentdate)
    {
        $this->commentdate = $commentdate;

        return $this;
    }

    /**
     * Method to set the value of field replyid
     *
     * @param integer $replyid
     * @return $this
     */
    public function setReplyId($replyid)
    {
        $this->replyid = $replyid;

        return $this;
    }

    /**
     * Returns the value of field commentid
     *
     * @return integer
     */
    public function getCommentId()
    {
        return $this->commentid;
    }

    /**
     * Returns the value of field commenttext
     *
     * @return string
     */
    public function getCommentText()
    {
        return $this->commenttext;
    }

    /**
     * Returns the value of field commentdate
     *
     * @return string
     */
    public function getCommentDate()
    {
        return $this->commentdate;
    }

    /**
     * Returns the value of field replyid
     *
     * @return integer
     */
    public function getReplyId()
    {
        return $this->replyid;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
    }

    /**
     * @param $comments - CommentsModel[], как массив объектов.
     * @return array $handledComments в виде массива с полями [deleted, commentdate, commentid, replyid, commenttext,
     *      publisherUser|publisherCompany]. commenttext и publisher могут отсутствовать, если deleted = true.
     */
    public static function handleComments($comments){
        $handledComments = [];
        foreach ($comments as $comment){
            $handledComment = [
                'commentdate' => $comment->getCommentDate(),
                'commentid' => $comment->getCommentId(),
                'replyid' => $comment->getReplyId()];

            if(!$comment->getDeleted()){
                $handledComment['commenttext'] = $comment->getCommentText();

                if($comment->getAccountId()!= null) {
                    if ($comment->accounts->getCompanyId() == null) {
                        /*$user = Userinfo::findFirst(
                            ['conditions' => 'user_id = :userId:',
                                'columns' => Userinfo::shortColumnsInStr,
                                'bind' => ['userId' => $comment->accounts->getUserId()]]);*/
                        $user = Userinfo::findUserInfoById($comment->accounts->getUserId(),Userinfo::shortColumnsInStr);
                        $handledComment['publisherUser'] = $user;
                    } else {
                        $company = Companies::findFirst(
                            ['conditions' => 'company_id = :companyId:',
                                'columns' => Companies::shortColumnsInStr,
                                'bind' => ['companyId' => $comment->accounts->getCompanyId()]]);
                        $company = Companies::findUserInfoById($comment->accounts->getCompanyId(),
                            Companies::shortColumnsInStr);
                        $handledComment['publisherCompany'] = $company;
                    }
                }
            } else{
                $handledComment['deleted'] = $comment->getDeleted();
            }

            //$handledComment['likes'] = count(LikesCommentsImagesUsers::findByCommentId($comment->getCommentId()));

            $handledComments[] = $handledComment;
        }
        return $handledComments;
    }
}
