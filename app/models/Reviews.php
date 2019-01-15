<?php

namespace App\Models;

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
    protected $text_review;

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
    protected $subjectid;
    protected $subjecttype;
    protected $objectid;
    protected $objecttype;

    const publicColumns = ['review_id', 'text_review', 'review_date', 'rating', 'binder_id',
        'binder_type', 'executor', 'fake_name',];

    /**
     * Методы-костыли
     */

    public function setSubjectId($subjectid)
    {
        $this->subjectid = $subjectid;

        return $this;
    }

    public function setSubjectType($subjecttype)
    {
        $this->subjecttype = $subjecttype;

        return $this;
    }

    public function setObjectId($objectid)
    {
        $this->objectid = $objectid;

        return $this;
    }

    public function setObjectType($objecttype)
    {
        $this->objecttype = $objecttype;

        return $this;
    }

    public function setFakeName($fake_name)
    {
        $this->fake_name = $fake_name;
        return $this;
    }

    public function getSubjectId()
    {
        return $this->subjectid;
    }

    public function getSubjectType()
    {
        return $this->subjecttype;
    }

    public function getObjectId()
    {
        return $this->objectid;
    }

    public function getFakeName()
    {
        return $this->fake_name;
    }

    public function getObjectType()
    {
        return $this->objecttype;
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
    public function setTextReview($textreview)
    {
        $this->text_review = $textreview;

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
        if ($bindertype == 0)
            $this->binder_type = 'task';
        else if ($bindertype == 1)
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
    public function getTextReview()
    {
        return $this->text_review;
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

        if ($this->getFake() == null || !$this->getFake())
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
            );

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

        if($result) {
            //$this->updateRating();
        }

        return $result;
    }

    public function delete($delete = false,$deletedCascade=false,$data = null, $whiteList = null)
    {
        $result = parent::delete($delete,$deletedCascade,$data, $whiteList);

        if($result) {
            //$this->updateRating();
        }

        return $result;
    }

    public function update($data = null, $whiteList = null)
    {
        $result = parent::update($data, $whiteList);

        if($result) {
            //$this->updateRating();
        }

        return $result;
    }

    public static function getReviewsForObject($subjectId, $subjectType){
        $db = Phalcon\DI::getDefault()->getDb();

        $query = $db->prepare("Select * FROM (
              --Отзывы оставленные на заказы данного субъекта
              (SELECT reviews.review_id as id,
              reviews.text_review as text,
              reviews.review_date as date,
              reviews.rating as rating,
              reviews.executor as executor
              FROM reviews inner join tasks 
              ON (reviews.binder_id= tasks.task_id AND reviews.binder_type = 'task' AND reviews.executor = true)
              WHERE tasks.subject_id = :subjectId AND tasks.subject_type = :subjectType)
              UNION
              --Отзывы оставленные на предложения данного субъекта
              (SELECT reviews.reviewId as id, 
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews inner join offers 
              ON (reviews.binderId = offers.taskId AND reviews.binderType = 'task'
                  AND reviews.executor = false AND offers.selected = true) 
              WHERE offers.subjectId = :subjectId AND offers.subjectType = :subjectType) 
              UNION
              --Отзывы оставленные на заявки
              (SELECT reviews.reviewId as id, 
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews inner join requests
              ON (reviews.binderId = requests.requestId AND reviews.binderType = 'request'
                  AND reviews.executor = true)
              WHERE requests.subjectId = :subjectId AND requests.subjectType = :subjectType) 
              UNION
              --Отзывы оставленные на услуги
              (SELECT reviews.reviewId as id, 
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM services inner join requests ON (requests.serviceId = services.serviceId)
              inner join reviews
              ON (reviews.binderId = requests.requestId AND reviews.binderType = 'request'
                  AND reviews.executor = false)
              WHERE services.subjectId = :subjectId AND services.subjectType = :subjectType)
              UNION
              --фейковые отзывы
              (SELECT reviews.reviewId as id, 
              reviews.textReview as text,
              reviews.reviewdate as date,
              reviews.rating as rating,
              reviews.executor as executor 
              FROM reviews
              WHERE reviews.objectId = :subjectId AND reviews.objectType = :subjectType)
              ) p0
              ORDER BY p0.date desc"
        );

        $query->execute([
            'subjectId' => $subjectId,
            'subjectType' => $subjectType,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getReviewsForService($serviceId, $limit = null){
        $db = DI::getDefault()->getDb();

        $str = "Select * FROM (
              --Отзывы оставленные на услуги
              (SELECT ";
        foreach(Reviews::publicColumns as $column){
            $str .= 'reviews.'.$column . ',';
        }
        $str[strlen($str)-1] = ' ';
        $str .= "FROM services inner join requests ON (requests.service_id = services.service_id)
              inner join reviews
              ON (reviews.binder_id = requests.request_id AND reviews.binderType = 'request'
                  AND reviews.executor = false)
              WHERE services.serviceId = :serviceId
              )
              UNION ALL
              (SELECT ";
        foreach(Reviews::publicColumns as $column){
            $str .= 'reviews.'.$column . ',';
        }
        $str[strlen($str)-1] = ' ';

        $str .= "FROM reviews where fake = true and bindertype = 'service' and binderid = :serviceId)
        ) p0 ORDER BY p0.reviewdate desc";

        if($limit != null && $limit > 0)
            $str.= " LIMIT ". $limit;

        $query = $db->prepare($str);

        $query->execute([
            'serviceId' => $serviceId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getReviewsForService2($serviceId, $limit = null){
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

        if($limit!= null && $limit > 0)
            $str.=' LIMIT '.$limit;
        $query = $db->prepare($str);


        $query->execute([
            'serviceId' => $serviceId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }



    private function updateRating(){
        if (!$this->getFake()) {
            if($this->getBinderType() == 'task'){
                if($this->getExecutor() === true){
                    $task = Tasks::findFirstByTaskid($this->getBinderId());

                    $subjectId = $task->getSubjectId();
                    $subjectType = $task->getSubjectType();
                } else{
                    $offer = Offers::findConfirmedOfferByTask($this->getBinderId());

                    $subjectId = $offer->getSubjectId();
                    $subjectType = $offer->getSubjectType();
                }
            } elseif($this->getBinderType() == 'request'){
                if($this->getExecutor() === true){
                    $request = Requests::findFirstByRequestid($this->getBinderId());
                    $subjectId = $request->getSubjectId();
                    $subjectType = $request->getSubjectType();
                } else{
                    $request = Requests::findFirstByRequestid($this->getBinderId());
                    $subjectId = $request->services->getSubjectId();
                    $subjectType = $request->services->getSubjectType();

                    $reviews = Reviews::getReviewsForService($request->services->getServiceId());
                    $sum = 5;
                    foreach($reviews as $review){
                        $sum+=$review['rating'];
                    }
                    $sum/=(count($reviews)+1);
                    $request->services->setRating($sum);
                    $request->services->update();
                }
            }

            $reviews = $this->getReviewsForObject($subjectId,$subjectType);
            $sum = 5;
            foreach($reviews as $review){
                $sum+=$review['rating'];
            }
            $sum/=(count($reviews)+1);
            if($subjectType == 0) {
                $userinfo = Userinfo::findFirstByUserid($subjectId);

                //$sum = (($this->getRating() * ($reviews->count() + 4)) + $sum) / ($reviews->count() + 5);

                if ($this->getExecutor() === false)
                    $userinfo->setRatingExecutor($sum);
                else
                    $userinfo->setRatingClient($sum);

                $userinfo->update();
            } elseif($subjectType == 1){
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
}
