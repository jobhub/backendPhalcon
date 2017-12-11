<?php

class Tasks extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $taskId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $userId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $categoryId;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=true)
     */
    public $description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $deadline;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $price;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("tasks");
        $this->hasMany('taskId', 'Auctions', 'taskId', ['alias' => 'Auctions']);
        $this->belongsTo('categoryId', '\Categories', 'categoryId', ['alias' => 'Categories']);
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
