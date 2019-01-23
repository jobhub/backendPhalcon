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
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $object_id;

    const DEFAULT_RESULT_PER_PAGE_PARENT = 10;
    const DEFAULT_RESULT_PER_PAGE_CHILD = 10;

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * @param int $object_id
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;
    }

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
                        $user = Userinfo::findUserInfoById($account->getUserId(),Userinfo::shortColumns);
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

    public static function findParentComments($model,$objectId,$page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE_PARENT){
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $comments = strval('App\Models\\'.$model)::find(['conditions'=>'object_id = :objectId: and reply_id is null',
            'bind' =>['objectId'=> $objectId],
            'order' => 'comment_date DESC',
            'limit'=>$page_size,
            'offset'=>$offset],false);

        $comments_arr =  self::handleComments($comments->toArray());
        /*for($i = 0; $i < count($comments_arr);$i++){
            $like_model = 'App\Models\Likes'.$model;
            $comments_arr[$i]['likes'] = count($like_model::findByCommentId($comments_arr[$i]['comment_id']));
        }*/
        return $comments_arr;
    }

    public static function findChildComments($model,$objectId,$parentId,$page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE_CHILD){
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $comments = strval('App\Models\\'.$model)::find(['conditions'=>'object_id = :objectId: and reply_id = :parentId:',
            'bind' =>['objectId'=> $objectId,'parentId'=>$parentId],
            'order' => 'comment_date DESC',
            'limit'=>$page_size,
            'offset'=>$offset],false);

        $comments_arr =  self::handleComments($comments->toArray());
        /*for($i = 0; $i < count($comments_arr);$i++){
            $like_model = 'App\Models\Likes'.$model;
            $comments_arr[$i]['likes'] = count($like_model::findByCommentId($comments_arr[$i]['comment_id']));
        }*/
        return $comments_arr;
    }
}
