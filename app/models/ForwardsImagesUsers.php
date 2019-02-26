<?php

namespace App\Models;

class ForwardsImagesUsers extends ForwardsInNewsModel
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
        $this->setSource("forwards_images_users");
        $this->belongsTo('object_id', 'App\Models\ImagesUsers', 'image_id', ['alias' => 'ImagesUsers']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'forwards_images_users';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ForwardsNews[]|ForwardsNews|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ForwardsNews|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findForwardByIds($accountId, $objectId)
    {
        return parent::findByIds('App\Models\ForwardsImagesUsers',$accountId,$objectId);
    }
}
