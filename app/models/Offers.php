<?php

class Offers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $offerId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $userId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $deadline;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $description;

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
        $this->setSource("offers");
        $this->hasMany('offerId', 'Auctions', 'selectedOffer', ['alias' => 'Auctions']);
        $this->belongsTo('userId', '\Users', 'userId', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'offers';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Offers[]|Offers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Offers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
