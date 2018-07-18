<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Tasks extends \Phalcon\Mvc\Model
{
    /**
     *
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
    protected $userId;

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
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $pointId;

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
     * Method to set the value of field userId
     *
     * @param integer $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

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
     * Method to set the value of field pointId
     *
     * @param integer $pointId
     * @return $this
     */
    public function setPointId($pointId)
    {
        $this->pointId = $pointId;

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
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
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
     * Returns the value of field pointId
     *
     * @return integer
     */
    public function getPointId()
    {
        return $this->pointId;
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
            'userId',
            new Callback(
                [
                    "message" => "Такой пользователь не существует",
                    "callback" => function ($request) {
                        $user = Users::findFirstByUserId($request->getUserId());
                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'categoryId',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($request) {
                        $service = Services::findFirstByServiceId($request->getServiceId());

                        if ($service)
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
                'callback' => function ($request) {
                    if($request->getStatus() < 0 || $request->getStatus() > 4)
                        return false;
                    return true;
                }
            ])
        );

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
        $this->belongsTo('pointId', '\TradePoints', 'pointId', ['alias' => 'Tradepoints']);
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

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Tasks[]|Tasks|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Tasks|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
