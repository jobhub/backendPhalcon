<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class ServicesTags extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $service_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $tag_id;

    /**
     * Method to set the value of field serviceid
     *
     * @param integer $serviceid
     * @return $this
     */
    public function setServiceId($serviceid)
    {
        $this->service_id = $serviceid;

        return $this;
    }

    /**
     * Method to set the value of field tagid
     *
     * @param integer $tagid
     * @return $this
     */
    public function setTagId($tagid)
    {
        $this->tag_id = $tagid;

        return $this;
    }

    /**
     * Returns the value of field serviceid
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Returns the value of field tagid
     *
     * @return integer
     */
    public function getTagId()
    {
        return $this->tag_id;
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
            'service_id',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($serviceTag) {
                        $service = Services::findFirstByServiceId($serviceTag->getServiceId());

                        if ($service)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'tag_id',
            new Callback(
                [
                    "message" => "Тег не создан",
                    "callback" => function ($serviceTag) {
                        $tag = Tags::findFirstByTagId($serviceTag->getTagId());
                        if ($tag)
                            return true;
                        return false;
                    }
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("services_tags");
        $this->belongsTo('service_id', 'App\Models\Services', 'service_id', ['alias' => 'Services']);
        $this->belongsTo('tag_id', 'App\Models\Tags', 'tag_id', ['alias' => 'Tags']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'services_tags';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ServicesTags[]|ServicesTags|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ServicesTags|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($serviceId, $tagId)
    {
        return ServicesTags::findFirst(['service_id = :serviceId: AND tag_id = :tagId:',
            'bind' => ['serviceId' => $serviceId, 'tagId' => $tagId]]);
    }
}
