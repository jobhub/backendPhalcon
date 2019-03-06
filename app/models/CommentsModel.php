<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use App\Libs\SupportClass;

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

    protected $likes;

    const DEFAULT_RESULT_PER_PAGE_PARENT = 10;
    const DEFAULT_RESULT_PER_PAGE_CHILD = 10;

    /**
     * @return string
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param string $likes
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
    }

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

    public static function findLastParentComment($model, $objectId)
    {
        $comment = $model::findFirst(['conditions' => 'object_id = :objectId: and reply_id is null',
            'bind' => ['objectId' => $objectId],
            'order' => 'comment_date DESC'
        ], false);

        if (!$comment)
            return [];
        return self::handleComments([$comment->toArray()],$model)[0];
    }

    /**
     * @param $comments - array.
     * @param $accountId - account's id for field liked.
     * @return array $handledComments в виде массива с полями [deleted, comment_date, comment_id, reply_id, comment_text,
     *      publisher_user|publisher_company]. comment_text и publisher могут отсутствовать, если deleted = true.
     */
    public static function handleComments(array $comments, $model, $accountId = null)
    {
        $handledComments = [];
        foreach ($comments as $comment) {
            $handledComment = [
                'comment_date' => $comment['comment_date'],
                'comment_id' => $comment['comment_id'],
                'reply_id' => $comment['reply_id']];

            if (!$comment['deleted']) {
                $handledComment['comment_text'] = $comment['comment_text'];

                if ($comment['account_id'] != null) {
                    $account = Accounts::findFirstById($comment['account_id']);

                    if ($account && $account->getCompanyId() == null) {
                        /*$user = Userinfo::findFirst(
                            ['conditions' => 'user_id = :userId:',
                                'columns' => Userinfo::shortColumnsInStr,
                                'bind' => ['userId' => $comment->accounts->getUserId()]]);*/
                        $user = Userinfo::findUserInfoById($account->getUserId(), Userinfo::shortColumns);
                        $handledComment['publisher_user'] = $user;
                    } else {
                        /*$company = Companies::findFirst(
                            ['conditions' => 'company_id = :companyId:',
                                'columns' => Companies::shortColumnsInStr,
                                'bind' => ['companyId' => $comment->accounts->getCompanyId()]]);*/
                        $company = Companies::findCompanyById($account->getCompanyId(),
                            Companies::shortColumns);
                        $handledComment['publisher_company'] = $company;
                    }
                }
            } else {
                $handledComment['deleted'] = $comment['deleted'];
            }
            $handledComment['child_count'] = self::getCountOfComments($model::getSource(),$comment['object_id'],
                '{'.$comment['comment_id'].'}');

            $handledComment = LikeModel::handleObjectWithLikes($handledComment, $comment, $accountId);
            $handledComments[] = $handledComment;
        }
        return $handledComments;
    }

    public static function findParentComments($model, $objectId, $page = 1,
                                              $page_size = self::DEFAULT_RESULT_PER_PAGE_PARENT, $accountId = null)
    {
        /*$page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $comments = strval('App\Models\\' . $model)::find(['conditions' => 'object_id = :objectId: and reply_id is null',
            'bind' => ['objectId' => $objectId],
            'order' => 'comment_date DESC',
            'limit' => $page_size,
            'offset' => $offset], false);*/

        $comments = SupportClass::executeWithPagination(['model'=>$model,
            'conditions' => 'object_id = :objectId: and reply_id is null',
            'bind' => ['objectId' => $objectId],
            'order' => 'comment_date DESC','deleted'=>false],null,$page,$page_size);

        $comments['data'] = self::handleComments($comments['data'], $model, $accountId);
        return $comments;
    }

    private static function findChildCommentsWithoutHandle($model, $objectId, $parentId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE_CHILD)
    {
        /*$page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $comments = strval('App\Models\\' . $model)::find(['conditions' => 'object_id = :objectId: and reply_id = :parentId:',
            'bind' => ['objectId' => $objectId, 'parentId' => $parentId],
            'order' => 'comment_date DESC',
            'limit' => $page_size,
            'offset' => $offset], false);*/

        $comments = SupportClass::executeWithPagination(['model'=>$model,
                'conditions' => 'object_id = :objectId: and reply_id = :parentId:',
                'bind' => ['objectId' => $objectId, 'parentId' => $parentId],
                'order' => 'comment_date DESC','deleted'=>false],null,$page,$page_size);

        return $comments;
    }

    public static function findChildComments($model, $objectId, $parentId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE_CHILD, $accountId = null)
    {
        $comments = self::findChildCommentsWithoutHandle($model, $objectId, $parentId, $page, $page_size);
        $comments['data'] = self::handleComments($comments['data'], $model, $accountId);

        return $comments;
    }

    /**
     * @param $model - name of table
     * @param null $parentIds
     * @param $objectId
     *
     * @return integer - count of comments
     */
    public static function getCountOfComments($model, $objectId, $parentIds = null){
        $db = DI::getDefault()->getDb();
        $count = 0;
        $ids = $parentIds;

        $sqlParent = 'SELECT COUNT(*) AS total, array_agg(comment_id) as ids FROM ' .
            $model . ' where reply_id is null and object_id = :objectId';


        $sqlChild = 'SELECT COUNT(*) AS total, array_agg(comment_id) as ids FROM ' .
            $model . ' where reply_id = ANY (:ids) and object_id = :objectId';

        do {
            if ($ids == null) {
                $query = $db->prepare($sqlParent);
                $query->execute([
                    'objectId' => $objectId
                ]);

                $result = $query->fetchAll(\PDO::FETCH_ASSOC);

                /*if ($result[0]['total'] == 0)
                    return $result[0]['total'];*/

                $count+=$result[0]['total'];
                $ids = $result[0]['ids'];
            } else {
                $query = $db->prepare($sqlChild);
                $query->execute([
                    'ids' => $ids,
                    'objectId' => $objectId
                ]);

                $result = $query->fetchAll(\PDO::FETCH_ASSOC);

                $count+=$result[0]['total'];
                $ids = $result[0]['ids'];
            }
        } while($result[0]['total']!=0);

        return $count;
    }
}
