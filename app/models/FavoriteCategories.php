<?php

class Favoritecategories extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $userId;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $categoryId;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("favoritecategories");
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
        return 'favoritecategories';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoritecategories[]|Favoritecategories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoritecategories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
