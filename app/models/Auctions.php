<?php

class Auctions extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $auctionId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $taskId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $selectedOffer;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $dateStart;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $dateEnd;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("auctions");
        $this->belongsTo('selectedOffer', '\Offers', 'offerId', ['alias' => 'Offers']);
        $this->belongsTo('taskId', '\Tasks', 'taskId', ['alias' => 'Tasks']);
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
