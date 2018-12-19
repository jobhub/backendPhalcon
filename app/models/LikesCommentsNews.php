<?php

class LikesCommentsNews extends AccountModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $comment_id;

    /**
     * Method to set the value of field account_id
     *
     * @param integer $account_id
     * @return $this
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;

        return $this;
    }

    /**
     * Method to set the value of field comment_id
     *
     * @param integer $comment_id
     * @return $this
     */
    public function setCommentId($comment_id)
    {
        $this->comment_id = $comment_id;

        return $this;
    }

    /**
     * Returns the value of field account_id
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Returns the value of field comment_id
     *
     * @return integer
     */
    public function getCommentId()
    {
        return $this->comment_id;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'account_id',
            new Callback(
                [
                    "message" => "Компания может оставить только один лайк, независимо от количества менеджеров",
                    "callback" => function ($likesModel) {

                        $result = self::findCommentLikedByCompany($likesModel->getAccountId(),$likesModel->getCommentId());

                        if (count($result)>0)
                            return false;
                        return true;
                    }
                ]
            )
        );


        return $this->validate($validator);
    }

    //Определяет, лайкал ли кто-нибудь из компании указанный комментарий.
    //Если да, то возвращает объект LikesCommentsImagesUsers, иначе null.
    public static function findCommentLikedByCompany($accountId, $commentId)
    {
        $modelsManager = Phalcon\DI::getDefault()->get('modelsManager');
        $account = Accounts::findFirstById($accountId);
        if(!$account || $account->getCompanyId() == null)
            return null;
        return $modelsManager->createBuilder()
            ->from(["a" => "Accounts"])
            ->join('LikesCommentsImagesUsers', 'a.id = likes.account_id', 'likes')
            ->where('a.company_id = :companyId: and likes.comment_id = :commentId:',
                ['companyId' => $account->getCompanyId(),
                    'commentId' => $commentId])
            ->getQuery()
            ->execute();
    }

    public static function findCommentLiked($accountId, $commentId)
    {
        return self::findFirst([
            'account_id = :accountId: and comment_id = :commentId:',
            'bind' => [
                'accountId' => $accountId,
                'commentId' => $commentId,
            ]
        ]);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("likes_comments_news");
        $this->belongsTo('account_id', '\Accounts', 'id', ['alias' => 'Accounts']);
        $this->belongsTo('comment_id', '\CommentsNews', 'commentid', ['alias' => 'CommentsNews']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'likes_comments_news';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return LikesCommentsNews[]|LikesCommentsNews|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return LikesCommentsNews|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
