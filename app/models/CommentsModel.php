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
    protected $comment_id;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=false)
     */
    protected $comment_text;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $comment_date;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $reply_id;

    /**
     * Method to set the value of field commentid
     *
     * @param integer $commentid
     * @return $this
     */
    public function setCommentId($commentid)
    {
        $this->comment_id = $commentid;

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
        $this->comment_text = $commenttext;

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
        $this->comment_date = $commentdate;

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
        $this->reply_id = $replyid;

        return $this;
    }

    /**
     * Returns the value of field commentid
     *
     * @return integer
     */
    public function getCommentId()
    {
        return $this->comment_id;
    }

    /**
     * Returns the value of field commenttext
     *
     * @return string
     */
    public function getCommentText()
    {
        return $this->comment_text;
    }

    /**
     * Returns the value of field commentdate
     *
     * @return string
     */
    public function getCommentDate()
    {
        return $this->comment_date;
    }

    /**
     * Returns the value of field replyid
     *
     * @return integer
     */
    public function getReplyId()
    {
        return $this->reply_id;
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
     * @return array $handledComments в виде массива с полями [deleted, comment_date, comment_id, reply_id, comment_text,
     *      publisher_user|publisher_company]. comment_text и publisher могут отсутствовать, если deleted = true.
     */
    public static function handleComments(array $comments){
        $handledComments = [];
        foreach ($comments as $comment){
            $handledComment = [
                'comment_date' => $comment['comment_date'],
                'comment_id' => $comment['comment_id'],
                'reply_id' => $comment['reply_id']];

            if(!$comment['deleted']){
                $handledComment['comment_text'] = $comment['comment_text'];

                if($comment['account_id']!= null) {
                    $account = Accounts::findFirstById($comment['account_id']);

                    if ($account && $account->getCompanyId() == null) {
                        /*$user = Userinfo::findFirst(
                            ['conditions' => 'user_id = :userId:',
                                'columns' => Userinfo::shortColumnsInStr,
                                'bind' => ['userId' => $comment->accounts->getUserId()]]);*/
                        $user = Userinfo::findUserInfoById($account->getUserId(),Userinfo::shortColumnsInStr);
                        $handledComment['publisher_user'] = $user;
                    } else {
                        /*$company = Companies::findFirst(
                            ['conditions' => 'company_id = :companyId:',
                                'columns' => Companies::shortColumnsInStr,
                                'bind' => ['companyId' => $comment->accounts->getCompanyId()]]);*/
                        $company = Companies::findCompanyById($account->getCompanyId(),
                            Companies::shortColumnsInStr);
                        $handledComment['publisher_company'] = $company;
                    }
                }
            } else{
                $handledComment['deleted'] = $comment['deleted'];
            }

            //$handledComment['likes'] = count(LikesCommentsImagesUsers::findByCommentId($comment->getCommentId()));

            $handledComments[] = $handledComment;
        }
        return $handledComments;
    }
}
