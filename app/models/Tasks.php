<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

use App\Libs\SupportClass;

class Tasks extends AccountWithNotDeletedWithCascade
{
    /**
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $task_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $category_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deadline;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $polygon;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $region_id;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=true)
     */
    protected $longitude;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=true)
     */
    protected $latitude;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $date_start;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $date_end;

    const publicColumns = ['task_id', 'category_id','name', 'description', 'deadline', 'price',
        'status', 'polygon', 'region_id', 'longitude', 'latitude', 'date_start', 'date_end'];

    /**
     * Method to set the value of field taskId
     *
     * @param integer $task_id
     * @return $this
     */
    public function setTaskId($task_id)
    {
        $this->task_id = $task_id;

        return $this;
    }

    /**
     * Method to set the value of field categoryId
     *
     * @param integer $category_id
     * @return $this
     */
    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field deadline
     *
     * @param string $deadline
     * @return $this
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Method to set the value of field dateStart
     *
     * @param string $date_start
     * @return $this
     */
    public function setDateStart($date_start)
    {
        $this->date_start = $date_start;

        return $this;
    }

    /**
     * Method to set the value of field dateEnd
     *
     * @param string $date_end
     * @return $this
     */
    public function setDateEnd($date_end)
    {
        $this->date_end = $date_end;

        return $this;
    }

    /**
     * Method to set the value of field price
     *
     * @param integer $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Method to set the value of field status
     *
     * @param integer $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Method to set the value of field polygon
     *
     * @param string $polygon
     * @return $this
     */
    public function setPolygon($polygon)
    {
        $this->polygon = $polygon;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $region_id
     * @return $this
     */
    public function setRegionId($region_id)
    {
        $this->region_id = $region_id;

        return $this;
    }

    /**
     * Method to set the value of field longitude
     *
     * @param string $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Method to set the value of field latitude
     *
     * @param string $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Returns the value of field taskId
     *
     * @return integer
     */
    public function getTaskId()
    {
        return $this->task_id;
    }

    /**
     * Returns the value of field categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field deadline
     *
     * @return string
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Returns the value of field dateStart
     *
     * @return string
     */
    public function getDateStart()
    {
        return $this->date_start;
    }

    /**
     * Returns the value of field dateEnd
     *
     * @return string
     */
    public function getDateEnd()
    {
        return $this->date_end;
    }

    /**
     * Returns the value of field price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the value of field status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the value of field polygon
     *
     * @return string
     */
    public function getPolygon()
    {
        return $this->polygon;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->region_id;
    }

    /**
     * Returns the value of field longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Returns the value of field latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
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
            'category_id',
            new Callback(
                [
                    "message" => "Такая категория не существует или она не является дочерней",
                    "callback" => function ($task) {
                        $category = Categories::findFirstByCategoryId($task->getCategoryId());

                        if ($category && ($category->getParentId() != null && $category->getParentId() != 0))
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'status',
            new Callback([
                "message" => "Поле статус имеет неверное значение.",
                'callback' => function ($task) {
                $status = Statuses::findFirstByStatusId($task->getStatus());
                    if (!$status)
                        return false;
                    return true;
                }
            ])
        );

        if($this->getLatitude() != null) {
            $validator->add(
                'latitude',
                new Callback([
                    "message" => "Не указана долгота",
                    'callback' => function ($task) {
                        if($task->getLongitude() == null)
                            return false;
                        return true;
                    }
                ])
            );
        }

        if($this->getLongitude() != null) {
            $validator->add(
                'longitude',
                new Callback([
                    "message" => "Не указана широта",
                    'callback' => function ($task) {
                        if($task->getLatitude() == null)
                            return false;
                        return true;
                    }
                ])
            );
        }

        $validator->add(
            'name',
            new PresenceOf([
                "message" => "Должно быть указано название задания"
            ])
        );

        $validator->add(
            'price',
            new Callback([
                "message" => "Должна быть указана цена",
                'callback' => function ($task) {
                    if($task->getPrice() == null ||
                        (!is_double($task->getPrice()) && !is_integer($task->getPrice())))
                        return false;
                    return true;
                }
            ])
        );

        if ($this->getRegionId() != null) {
            $validator->add(
                'region_id',
                new Callback(
                    [
                        "message" => "Указанный регион не существует",
                        "callback" => function ($task) {

                            if ($task->regions != null)
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        $validator->add(
            'date_start',
            new PresenceOf([
                "message" => "Дата начала приема заявок должна быть указана"
            ])
        );

        $validator->add(
            'date_end',
            new PresenceOf([
                "message" => "Дата завершения приема заявок должна быть указана"
            ])
        );

        $validator->add(
            'date_end',
            new Callback(
                [
                    "message" => "Дата завершения приема заявок должна быть не раньше даты начала и не позже даты завершения задания",
                    "callback" => function ($task) {
                        if (strtotime($task->getDateStart()) < strtotime($task->getDateEnd())
                                && strtotime($task->getDateEnd()) < strtotime($task->getDeadline()))
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
        $this->setSource("tasks");
        $this->belongsTo('category_id', 'App\Models\Categories', 'category_id', ['alias' => 'Categories']);
        $this->belongsTo('region_id', 'App\Models\Regions', 'region_id', ['alias' => 'Regions']);
        $this->belongsTo('status', 'App\Models\Statuses', 'status_id', ['alias' => 'Statuses']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'tasks';
    }

    public function getSequenceName()
    {
        return "tasks_taskid_seq";
    }

    public static function findTasksByCompany($companyId)
    {
        $result = self::findByCompany($companyId,"App\Models\Tasks",self::publicColumns);
        return self::handleTaskFromArray($result->toArray());
    }

    public static function findTasksByUser($userId)
    {
        $result = self::findByUser($userId,"App\Models\Tasks",self::publicColumns);

        return self::handleTaskFromArray($result->toArray());
    }

    public static function findAcceptingTasksByUser($userId)
    {
        $result = self::findByUser($userId,"App\Models\Tasks",self::publicColumns,
            ['status = :status:'],['status'=>STATUS_ACCEPTING]);

        return self::handleTaskFromArray($result->toArray());
    }

    public static function findAcceptingTasksByCompany($companyId)
    {
        $result = self::findByCompany($companyId,"App\Models\Tasks",self::publicColumns,
            ['status = :status:'],['status'=>STATUS_ACCEPTING]);

        return self::handleTaskFromArray($result->toArray());
    }



    public static function handleTaskFromArray(array $tasks){
        return $tasks;
    }

    /*public static function checkUserHavePermission($userId, $taskId, $right = null)
    {
        $task = Tasks::findFirstByTaskid($taskId);
        $user = Users::findFirstByUserid($userId);

        if (!$task)
            return false;

        if ($task->getSubjectType() == 1) {
            //Предложение компании
            $rightCompany = 'Tasks';
            if($right == 'delete')
                $rightCompany = 'deleteTask';
            else if($right == 'get')
                $rightCompany = 'getTasks';
            else if($right == 'edit')
                $rightCompany = 'editTask';
            else if($right == 'getOffers')
                $rightCompany = 'getOffersForTask';

            if (!Companies::checkUserHavePermission($userId, $task->getSubjectId(), $rightCompany)) {
                return false;
            }
            return true;
        } else if ($task->getSubjectType() == 0) {
            if ($task->getSubjectId() != $userId && $user->getRole() != ROLE_MODERATOR) {
                return false;
            }
            return true;
        }
        return false;
    }*/

    public function beforeDelete(){
        //Проверка, можно ли удалить задание
        $offers = Offers::findByTaskId($this->getTaskId());
        if(count($offers)!= 0)
            return false;
        return true;
    }
}
