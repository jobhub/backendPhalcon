<?php

namespace Users;

class Auctions extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $auction_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $date_start;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $date_end;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $task_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $selected_offer;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("auctions");
        $this->belongsTo('selected_offer', 'Users\Offers', 'offer_id', ['alias' => 'Offers']);
        $this->belongsTo('task_id', 'Users\Tasks', 'task_id', ['alias' => 'Tasks']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'auctions';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Auctions[]|Auctions|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Auctions|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
