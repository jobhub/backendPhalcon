<?php

namespace App\Models;

use App\Libs\SupportClass;
use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Reviews extends NotDeletedModelWithCascade
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $review_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $review_text;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $review_date;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $rating;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $fake;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $binder_id;

    /**
     *
     * @var integer
     * @Column(type="string", length=10, nullable=false)
     */
    protected $binder_type;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $executor;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length = 180, nullable=true)
     */
    protected $fake_name;

    //куча костылей для того, чтобы можно было писать фейковые отзывы. Жесть
    protected $subject_account_id;
    protected $object_account_id;

    const publicColumns = ['review_id', 'review_text', 'review_date', 'rating', 'binder_id',
        'binder_type', 'executor', 'fake_name',];

    const DEFAULT_RESULT_PER_PAGE = 10;

    /**
     * Методы-костыли
     */

    public function setSubjectAccountId($subjectAccountId)
    {
        $this->subject_account_id = $subjectAccountId;

        return $this;
    }

    public function setObjectAccountId($objectAccountId)
    {
        $this->object_account_id = $objectAccountId;

        return $this;
    }

    public function setFakeName($fake_name)
    {
        $this->fake_name = $fake_name;
        return $this;
    }

    public function getSubjectId()
    {
        return $this->subject_account_id;
    }

    public function getObjectId()
    {
        return $this->object_account_id;
    }

    public function getFakeName()
    {
        return $this->fake_name;
    }

    /**
     * Method to set the value of field reviewId
     *
     * @param integer $reviewid
     * @return $this
     */
    public function setReviewId($reviewid)
    {
        $this->review_id = $reviewid;

        return $this;
    }

    /**
     * Method to set the value of field textReview
     *
     * @param string $textreview
     * @return $this
     */
    public function setReviewText($textreview)
    {
        $this->review_text = $textreview;

        return $this;
    }

    /**
     * Method to set the value of field reviewDate
     *
     * @param string $reviewdate
     * @return $this
     */
    public function setReviewDate($reviewdate)
    {
        $this->review_date = $reviewdate;

        return $this;
    }

    /**
     * Method to set the value of field rating
     *
     * @param integer $rating
     * @return $this
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Method to set the value of field fake
     *
     * @param string $fake
     * @return $this
     */
    public function setFake($fake)
    {
        $this->fake = $fake;

        return $this;
    }

    /**
     * Method to set the value of field binderId
     *
     * @param integer $binderid
     * @return $this
     */
    public function setBinderId($binderid)
    {
        $this->binder_id = $binderid;

        return $this;
    }

    /**
     * Method to set the value of field binderType
     *
     * @param integer $bindertype
     * @return $this
     */
    public function setBinderType($bindertype)
    {
        if ($bindertype == 1)
            $this->binder_type = 'task';
        else if ($bindertype == 2)
            $this->binder_type = 'request';
        $this->binder_type = $bindertype;
        return $this;
    }

    /**
     * Method to set the value of field executor
     *
     * @param string $executor
     * @return $this
     */
    public function setExecutor($executor)
    {
        $this->executor = $executor;

        return $this;
    }

    /**
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserId($userid)
    {
        $this->user_id = $userid;

        return $this;
    }

    /**
     * Returns the value of field idReview
     *
     * @return integer
     */
    public function getReviewId()
    {
        return $this->review_id;
    }

    /**
     * Returns the value of field textReview
     *
     * @return string
     */
    public function getReviewText()
    {
        return $this->review_text;
    }

    /**
     * Returns the value of field reviewDate
     *
     * @return string
     */
    public function getReviewDate()
    {
        return $this->review_date;
    }

    /**
     * Returns the value of field rating
     *
     * @return integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Returns the value of field fake
     *
     * @return string
     */
    public function getFake()
    {
        return $this->fake;
    }

    /**
     * Returns the value of field binderId
     *
     * @return integer
     */
    public function getBinderId()
    {
        return $this->binder_id;
    }

    /**
     * Returns the value of field binderType
     *
     * @return integer
     */
    public function getBinderType()
    {
        return $this->binder_type;
    }

    /**
     * Returns the value of field executor
     *
     * @return string
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if ($this->getFake() == null || !$this->getFake())
            $validator->add(
                'binder_id',
                new Callback(
                    [
                        "message" => "Такой объект не существует",
                        "callback" => function ($review) {
                            return Binders::checkBinderExists($review->getBinderId(), $review->getBinderType());
                        }
                    ]
                )
            );
        $validator->add(
            'rating',
            new Callback(
                [
                    "message" => "Рейтинг должен быть от 0 до 5",
                    "callback" => function ($review) {
                        return $review->getRating() <= 5 && $review->getRating() >= 0;
                    }
                ]
            )
        );

        /*if ($this->getFake() == null || !$this->getFake())
            $validator->add(
                'user_id',
                new Callback(
                    [
                        "message" => "Такой пользователь не существует",
                        "callback" => function ($review) {
                            if ($review->users != null)
                                return true;
                            return false;
                        }
                    ]
                )
            );*/

        $validator->add(
            'review_date',
            new Callback(
                [
                    "message" => "Время написания отзыва должно быть раньше текущего",
                    "callback" => function ($review) {
                        if (strtotime($review->getReviewDate()) <= time())
                            return true;
                        return false;
                    }
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("reviews");
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    public function save($data = null, $whiteList = null)
    {
        $result = parent::save($data, $whiteList);

        if ($result) {
            //$this->updateRating();
        }

        return $result;
    }

    public function delete($delete = false, $deletedCascade = false, $data = null, $whiteList = null)
    {
        $result = parent::delete($delete, $deletedCascade, $data, $whiteList);

        if ($result) {
            //$this->updateRating();
        }

        return $result;
    }

    public function update($data = null, $whiteList = null)
    {
        $result = parent::update($data, $whiteList);

        if ($result) {
            //$this->updateRating();
        }

        return $result;
    }

    public static function findReviewsByUser($userId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {

        $columns = '';
        foreach (self::publicColumns as $publicColumn) {
            if ($columns != '')
                $columns .= ', ';
            $columns .= 'reviews.' . $publicColumn;
        }

        /*$query = "Select * FROM (
              --Отзывы оставленные на заказы данного субъекта
              (SELECT " . $columns . "
              FROM reviews inner join tasks 
              ON (reviews.binder_id= tasks.task_id AND reviews.binder_type = 'task' AND reviews.executor = true)
              inner join accounts on (tasks.account_id = accounts.id and accounts.company_id is null)
              WHERE accounts.user_id = :userid1)
              UNION
              --Отзывы оставленные на предложения данного субъекта
              (SELECT " . $columns . "
              FROM reviews inner join offers 
              ON (reviews.binder_id = offers.task_id AND reviews.binder_type = 'task'
                  AND reviews.executor = false AND offers.selected = true) 
              inner join accounts on (offers.account_id = accounts.id and accounts.company_id is null)
              WHERE accounts.user_id = :userid2) 
              UNION
              --Отзывы оставленные на заявки
              (SELECT " . $columns . "
              FROM reviews inner join requests
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = true)
              inner join accounts on (requests.account_id = accounts.id and accounts.company_id is null)
              WHERE accounts.user_id = :userid3)
              UNION
              --Отзывы оставленные на услуги
              (SELECT " . $columns . "
              FROM services inner join requests ON (requests.service_id = services.service_id)
              inner join reviews
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = false)
              inner join accounts on (services.account_id = accounts.id and accounts.company_id is null)
              WHERE accounts.user_id = :userid4)
              UNION
              --фейковые отзывы
              (SELECT " . $columns . "
              FROM reviews
              inner join accounts on (reviews.object_account_id = accounts.id)
              WHERE accounts.user_id = :userid5)
              ) p0
              ORDER BY p0.review_date desc";*/

        $query = self::getQueryForFindReviewsByUser($userId,$columns);

        $str = SupportClass::formQuery($query);

        $reviews = SupportClass::executeWithPagination($str,
            ['userid1' => $userId,'userid2' => $userId,'userid3' => $userId,'userid4' => $userId,'userid5' => $userId,],
            $page,$page_size);

        $reviews['data'] =  self::handleReviewsFromArray($reviews['data']);
        return $reviews;
    }

    public static function findReviewsByCompany($companyId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $columns = '';
        foreach (self::publicColumns as $publicColumn) {
            if($columns == ''){
                $columns .= 'reviews.' . $publicColumn;
            } else
                $columns .= ', reviews.' . $publicColumn;
        }
        //$columns[strlen($columns) - 1] = '';

        /*$str = "Select * FROM (
              --Отзывы оставленные на заказы данного субъекта
              (SELECT " . $columns . "
              FROM reviews inner join tasks 
              ON (reviews.binder_id= tasks.task_id AND reviews.binder_type = 'task' AND reviews.executor = true)
              inner join accounts on (tasks.account_id = accounts.id)
              WHERE accounts.company_id = :companyId)
              UNION
              --Отзывы оставленные на предложения данного субъекта
              (SELECT " . $columns . "
              FROM reviews inner join offers 
              ON (reviews.binder_id = offers.task_id AND reviews.binder_type = 'task'
                  AND reviews.executor = false AND offers.selected = true) 
              inner join accounts on (offers.account_id = accounts.id)
              WHERE accounts.company_id = :companyId) 
              UNION
              --Отзывы оставленные на заявки
              (SELECT " . $columns . "
              FROM reviews inner join requests
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = true)
              inner join accounts on (requests.account_id = accounts.id)
              WHERE accounts.company_id = :companyId)
              UNION
              --Отзывы оставленные на услуги
              (SELECT " . $columns . "
              FROM services inner join requests ON (requests.service_id = services.service_id)
              inner join reviews
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = false)
              inner join accounts on (services.account_id = accounts.id)
              WHERE accounts.company_id = :companyId)
              UNION
              --фейковые отзывы
              (SELECT " . $columns . "
              FROM reviews
              inner join accounts on (reviews.object_account_id = accounts.id)
              WHERE accounts.company_id = :companyId)
              ) p0
              ORDER BY p0.review_date desc";*/

        $query = self::getQueryForFindReviewsByCompany($companyId,$columns);

        $str = SupportClass::formQuery($query);

        $reviews = SupportClass::executeWithPagination($str,
            ['companyId' => $companyId],$page,$page_size);

        $reviews['data'] =  self::handleReviewsFromArray($reviews['data']);
        return $reviews;
    }

    public static function getQueryForFindReviewsByUser($userId,$columns){
        return [
            'where' => '',
            'order' => 'p0.review_date desc',
            'from' => "(
              --Отзывы оставленные на заказы данного субъекта
              (SELECT " . $columns . "
              FROM reviews inner join tasks 
              ON (reviews.binder_id= tasks.task_id AND reviews.binder_type = 'task' AND reviews.executor = true)
              inner join accounts on (tasks.account_id = accounts.id and accounts.company_id is null)
              WHERE accounts.user_id = :userid1)
              UNION
              --Отзывы оставленные на предложения данного субъекта
              (SELECT " . $columns . "
              FROM reviews inner join offers 
              ON (reviews.binder_id = offers.task_id AND reviews.binder_type = 'task'
                  AND reviews.executor = false AND offers.selected = true) 
              inner join accounts on (offers.account_id = accounts.id and accounts.company_id is null)
              WHERE accounts.user_id = :userid2) 
              UNION
              --Отзывы оставленные на заявки
              (SELECT " . $columns . "
              FROM reviews inner join requests
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = true)
              inner join accounts on (requests.account_id = accounts.id and accounts.company_id is null)
              WHERE accounts.user_id = :userid3)
              UNION
              --Отзывы оставленные на услуги
              (SELECT " . $columns . "
              FROM services inner join requests ON (requests.service_id = services.service_id)
              inner join reviews
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = false)
              inner join accounts on (services.account_id = accounts.id and accounts.company_id is null)
              WHERE accounts.user_id = :userid4)
              UNION
              --фейковые отзывы
              (SELECT " . $columns . "
              FROM reviews
              inner join accounts on (reviews.object_account_id = accounts.id)
              WHERE accounts.user_id = :userid5)
              ) p0",
            'bind' => ['userid1' => $userId,'userid2' => $userId,'userid3' => $userId,'userid4' => $userId,'userid5' => $userId,],
            'columns_map' => [
                'review_date' => 'p0.review_date',
                'review_id' => 'p0.review_id',
            ],
            'id' => 'p0.review_id'];
    }

    public static function getQueryForFindReviewsByCompany($companyId,$columns){
        return [
            'where' => '',
            'order' => 'p0.review_date desc',
            'from' => "(
              --Отзывы оставленные на заказы данного субъекта
              (SELECT " . $columns . "
              FROM reviews inner join tasks 
              ON (reviews.binder_id= tasks.task_id AND reviews.binder_type = 'task' AND reviews.executor = true)
              inner join accounts on (tasks.account_id = accounts.id)
              WHERE accounts.company_id = :companyId)
              UNION
              --Отзывы оставленные на предложения данного субъекта
              (SELECT " . $columns . "
              FROM reviews inner join offers 
              ON (reviews.binder_id = offers.task_id AND reviews.binder_type = 'task'
                  AND reviews.executor = false AND offers.selected = true) 
              inner join accounts on (offers.account_id = accounts.id)
              WHERE accounts.company_id = :companyId) 
              UNION
              --Отзывы оставленные на заявки
              (SELECT " . $columns . "
              FROM reviews inner join requests
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = true)
              inner join accounts on (requests.account_id = accounts.id)
              WHERE accounts.company_id = :companyId)
              UNION
              --Отзывы оставленные на услуги
              (SELECT " . $columns . "
              FROM services inner join requests ON (requests.service_id = services.service_id)
              inner join reviews
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = false)
              inner join accounts on (services.account_id = accounts.id)
              WHERE accounts.company_id = :companyId)
              UNION
              --фейковые отзывы
              (SELECT " . $columns . "
              FROM reviews
              inner join accounts on (reviews.object_account_id = accounts.id)
              WHERE accounts.company_id = :companyId)
              ) p0",
            'bind' => ['companyId' => $companyId],
            'columns_map' => [
                'review_date' => 'p0.review_date',
                'review_id' => 'p0.review_id',
            ],
            'id' => 'p0.review_id'];
    }

    public static function reviewAlreadyExists($binderId, $binderType, $executor)
    {
        $review = Reviews::findFirst(['binder_id = :binderId: AND binder_type = :binderType: AND executor = :executor:',
            'bind' => ['binderId' => $binderId, 'binderType' => $binderType, 'executor' => $executor ? "true" : "false"]]);

        if ($review)
            return true;
        return false;
    }

    /*public static function findReviewsForObject($object_account_id)
    {
        $db = DI::getDefault()->getDb();

        $query = $db->prepare("Select * FROM (
              --Отзывы оставленные на заказы данного субъекта
              (SELECT reviews.review_id as id,
              reviews.text_review as text,
              reviews.review_date as date,
              reviews.rating as rating,
              reviews.executor as executor
              FROM reviews inner join tasks 
              ON (reviews.binder_id= tasks.task_id AND reviews.binder_type = 'task' AND reviews.executor = true)
              WHERE tasks.account_id = :accountId)
              UNION
              --Отзывы оставленные на предложения данного субъекта
              (SELECT reviews.review_id as id, 
              reviews.text_review as text,
              reviews.review_date as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews inner join offers 
              ON (reviews.binder_id = offers.task_id AND reviews.binder_type = 'task'
                  AND reviews.executor = false AND offers.selected = true) 
              WHERE offers.account_id = :account_id) 
              UNION
              --Отзывы оставленные на заявки
              (SELECT reviews.review_id as id, 
              reviews.text_review as text,
              reviews.review_date as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews inner join requests
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = true)
              WHERE requests.account_id = :account_id) 
              UNION
              --Отзывы оставленные на услуги
              (SELECT reviews.review_id as id, 
              reviews.text_review as text,
              reviews.review_date as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM services inner join requests ON (requests.service_id = services.service_id)
              inner join reviews
              ON (reviews.binder_id = requests.request_id AND reviews.binder_type = 'request'
                  AND reviews.executor = false)
              WHERE services.account_id = :account_id)
              UNION
              --фейковые отзывы
              (SELECT reviews.review_id as id, 
              reviews.text_review as text,
              reviews.review_date as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews
              WHERE reviews.object_account_id = :account_id)
              ) p0
              ORDER BY p0.date desc"
        );

        $query->execute([
            'account_id' => $object_account_id,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }*/

    public static function findReviewsForService($serviceId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        /*$page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;*/

        $modelsManager = DI::getDefault()->get('modelsManager');
        $columns = [];
        foreach (self::publicColumns as $publicColumn) {
            $columns[] = 'rev.' . $publicColumn;
        }
        $result = $modelsManager->createBuilder()
            ->columns($columns)
            ->from(["rev" => 'App\Models\Reviews'])
            ->join('App\Models\Requests', 'req.request_id = rev.binder_id and rev.binder_type = "request" and executor = false', 'req')
            ->join('App\Models\Services', 's.service_id = req.service_id', 's')
            ->where('s.service_id = :serviceId:', ['serviceId' => $serviceId]);
            /*->limit($page_size)
            ->offset($offset)*/
            /*->getQuery()
            ->execute();*/
        $reviews = SupportClass::executeWithPagination($result,
            ['serviceId' => $serviceId],$page,$page_size);

        $reviews['data'] =  self::handleReviewsFromArray($reviews['data']);
        return $reviews;

        //return self::handleReviewsFromArray($result->toArray());
    }

    /*public static function getReviewsForService2($serviceId, $limit = null)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $str = "Select review, subject FROM (
              --Отзывы оставленные на услуги
              (SELECT row_to_json(reviews.*) as review, row_to_json(subject.*) as subject, reviews.reviewdate as date
              FROM services inner join requests ON (requests.serviceId = services.serviceId)
              inner join reviews
              ON (reviews.binderId = requests.requestId AND reviews.binderType = 'request'
                  AND reviews.executor = false)
              
               INNER JOIN companies as subject ON (requests.subjectid = subject.companyid and requests.subjecttype = 1)
              
              WHERE services.serviceId = :serviceId
              )
			UNION ALL
    		(SELECT row_to_json(reviews.*) as review, row_to_json(subject.*) as subject, reviews.reviewdate as date
              FROM services inner join requests ON (requests.serviceId = services.serviceId)
              inner join reviews
              ON (reviews.binderId = requests.requestId AND reviews.binderType = 'request'
                  AND reviews.executor = false)
               INNER JOIN userinfo as subject ON (requests.subjectid = subject.userid and requests.subjecttype = 0)
              
              WHERE services.serviceId = :serviceId
              )
              UNION ALL
              (SELECT row_to_json(reviews.*) as review, row_to_json(reviews.*) as subject, reviews.reviewdate as date
              FROM reviews
              WHERE reviews.binderId = :serviceId and reviews.bindertype = 'service' and reviews.fake = true)
            ) p0 ORDER BY p0.date";

        if ($limit != null && $limit > 0)
            $str .= ' LIMIT ' . $limit;
        $query = $db->prepare($str);


        $query->execute([
            'serviceId' => $serviceId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }*/

    public static function handleReviewsFromArray(array $reviews)
    {
        $handled_reviews = [];
        foreach ($reviews as $review) {
            if ($review['binder_type'] == 'task') {
                $task = Tasks::findByTaskId($review['binder_id']);

                if (!$task)
                    continue;
                if($review['executor']){
                    $offer = Offers::findConfirmedOfferByTask($task->getTaskId());
                    $account = $offer->accounts;
                }else
                    $account = $task->accounts;

            } elseif ($review['binder_type'] == 'request') {
                $request = Requests::findFirstByRequestId($review['binder_id']);

                if (!$request)
                    continue;
                if($review['executor']) {
                    $account = $request->services->accounts;
                } else{
                    $account = $request->accounts;
                }
            }

            if($account!=null) {
                if ($account->getCompanyId() == null) {
                    $review['publisher_user'] = Userinfo::findUserInfoById($account->getUserId(), Userinfo::shortColumns);
                } else {
                    $review['publisher_company'] = Companies::findCompanyById($account->getCompanyId(), Companies::shortColumns);
                }
            }


            $review['images'] = ImagesReviews::findImagesForReview($review['review_id']);

            $handled_reviews[] = $review;
        }

        return $handled_reviews;
    }

    /**
     * It is not used now.
     */
    private function updateRating()
    {
        if (!$this->getFake()) {
            if ($this->getBinderType() == 'task') {
                if ($this->getExecutor() === true) {
                    $task = Tasks::findFirstByTaskid($this->getBinderId());

                    $subjectId = $task->getSubjectId();
                    $subjectType = $task->getSubjectType();
                } else {
                    $offer = Offers::findConfirmedOfferByTask($this->getBinderId());

                    $subjectId = $offer->getSubjectId();
                    $subjectType = $offer->getSubjectType();
                }
            } elseif ($this->getBinderType() == 'request') {
                if ($this->getExecutor() === true) {
                    $request = Requests::findFirstByRequestid($this->getBinderId());
                    $subjectId = $request->getSubjectId();
                    $subjectType = $request->getSubjectType();
                } else {
                    $request = Requests::findFirstByRequestid($this->getBinderId());
                    $subjectId = $request->services->getSubjectId();
                    $subjectType = $request->services->getSubjectType();

                    $reviews = Reviews::findReviewsForService($request->services->getServiceId());
                    $sum = 5;
                    foreach ($reviews as $review) {
                        $sum += $review['rating'];
                    }
                    $sum /= (count($reviews) + 1);
                    $request->services->setRating($sum);
                    $request->services->update();
                }
            }

            $reviews = $this->findReviewsForObject($subjectId, $subjectType);
            $sum = 5;
            foreach ($reviews as $review) {
                $sum += $review['rating'];
            }
            $sum /= (count($reviews) + 1);
            if ($subjectType == 0) {
                $userinfo = Userinfo::findFirstByUserid($subjectId);

                //$sum = (($this->getRating() * ($reviews->count() + 4)) + $sum) / ($reviews->count() + 5);

                if ($this->getExecutor() === false)
                    $userinfo->setRatingExecutor($sum);
                else
                    $userinfo->setRatingClient($sum);

                $userinfo->update();
            } elseif ($subjectType == 1) {
                $company = Companies::findFirstByCompanyid($subjectId);

                //$sum = (($this->getRating() * ($reviews->count() + 4)) + $sum) / ($reviews->count() + 5);

                if ($this->getExecutor() === false)
                    $company->setRatingExecutor($sum);
                else
                    $company->setRatingClient($sum);

                $company->update();
            }
        }
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'reviews';
    }

    public function getSequenceName()
    {
        return "reviews_reviewid_seq";
    }
}
