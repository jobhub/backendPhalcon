<?php

namespace App\Models;

class ForwardsProducts extends ForwardsInNewsModel
{
    public function validation()
    {
        return parent::validation();
    }
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("forwards_products");
        $this->belongsTo('object_id', 'App\Models\Products', 'product_id', ['alias' => 'Products']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'forwards_products';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ForwardsProducts[]|ForwardsProducts|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ForwardsProducts|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findForwardByIds($accountId, $objectId)
    {
        return parent::findByIds(get_class(),$accountId,$objectId);
    }
}
