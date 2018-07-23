<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Tasks extends NotDeletedModel
{
    /**
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $taskId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $categoryId;

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
    protected $regionId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deleted;

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
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectType;

    /**
     * Method to set the value of field taskId
     *
     * @param integer $taskId
     * @return $this
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * Method to set the value of field subjectId
     *
     * @param integer $subjectId
     * @return $this
     */
    public function setSubjectId($subjectId)
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function setSubjectType($subjectType)
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    /**
     * Method to set the value of field categoryId
     *
     * @param integer $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

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
     * @param integer $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * Method to set the value of field deleted
     *
     * @param string $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

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
        return $this->taskId;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectType()
    {
        return $this->subjectType;
    }

    /**
     * Returns the value of field categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
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
        return $this->regionId;
    }

    /**
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
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
            'subjectId',
            new Callback(
                [
                    "message" => "Такой субъект не существует",
                    "callback" => function ($task) {
                    if($task->getSubjectType() == 0) {
                        $user = Users::findFirstByUserId($task->getSubjectId());
                        if ($user)
                            return true;
                        return false;
                    } else if($task->getSubjectType() == 1){
                        $company = Companies::findFirstByCompanyId($task->getSubjectId());
                        if ($company)
                            return true;
                        return false;
                    } else
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'categoryId',
            new Callback(
                [
                    "message" => "Такая категория не существует или она не является дочерней",
                    "callback" => function ($task) {
                        $category = Categories::findFirstByCategoryId($task->getCategoryId());

                        if ($category && ($category->getParentId() == null || $category->getParentId() == 0))
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'status',
            new Callback([
                "message" => "Поле статус имеет неверное значение. Должно быть одно из следующих:\n0 - запрос в рассмотрении;\n
                1 - запрос выполняется;\n, 2 - запрос отклонен;\n, 3 - запрос выполнен;\n 4 - запрос не выполнен.",
                'callback' => function ($task) {
                    if ($task->getStatus() < 0 || $task->getStatus() > 4)
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
            new PresenceOf([
                "message" => "Цена должна быть указана"
            ])
        );

        if ($this->getRegionId() != null) {
            $validator->add(
                'regionId',
                new Callback(
                    [
                        "message" => "Указанный регион не существует",
                        "callback" => function ($task) {
                            $region = Regions::findFirstByRegionId($task->getRegionId());

                            if ($region)
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("tasks");
        $this->belongsTo('categoryId', '\Categories', 'categoryId', ['alias' => 'Categories']);
        $this->belongsTo('regionId', '\Regions', 'regionId', ['alias' => 'Regions']);
        $this->belongsTo('status', '\Statuses', 'statusId', ['alias' => 'Statuses']);
        $this->belongsTo('userId', '\Users', 'userId', ['alias' => 'Users']);
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

    public static function checkUserHavePermission($userId, $taskId, $right = null)
    {
        $task = Tasks::findFirstByTaskId($taskId);
        $user = Users::findFirstByUserId($userId);

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
    }
}
