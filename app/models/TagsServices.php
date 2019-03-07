<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class TagsServices extends TagsModel
{

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'object_id',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($serviceTag) {
                        $service = Services::findFirstByServiceId($serviceTag->getObjectId());

                        if ($service)
                            return true;
                        return false;
                    }
                ]
            )
        );

        return parent::validation()&&$this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("tags_services");
        $this->belongsTo('object_id', 'App\Models\Services', 'service_id', ['alias' => 'Services']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'tags_services';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TagsServices[]|TagsServices|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TagsServices|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /*public static function findByIds($serviceId, $tagId)
    {
        return TagsServices::findFirst(['object_id = :objectId: AND tag_id = :tagId:',
            'bind' => ['objectId' => $serviceId, 'tagId' => $tagId]]);
    }*/
}
