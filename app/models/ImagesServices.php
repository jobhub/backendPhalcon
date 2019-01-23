<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesServices extends ImagesModel
{

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $service_id;

    const MAX_IMAGES = 10;

    /**
     * Method to set the value of field serviceid
     *
     * @param integer $service_id
     * @return $this
     */
    public function setServiceId($service_id)
    {
        $this->service_id = $service_id;

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
                    "callback" => function ($image) {
                        $service = Services::findFirstByServiceId($image->getServiceId());
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
        $this->setSource("imagesservices");
        $this->belongsTo('service_id', 'App\Models\Services', 'service_id', ['alias' => 'Services']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'imagesservices';
    }

    public function getSequenceName()
    {
        return "imagesservices_imageid_seq";
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesServices[]|ImagesServices|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesServices|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function save($data = null, $whiteList = null)
    {
        $result = parent::save($data, $whiteList);
        return $result;
    }

    /**
     * return non formatted images objects
     * @param $serviceId
     * @param $page
     * @param $page_size
     * @return mixed
     */
    public static function findImagesForService($serviceId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE){
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        return self::handleImages(
            self::find(['conditions'=>'service_id = :serviceId:','bind'=>['serviceId'=>$serviceId],
                'limit'=>$page_size,'offset'=>$offset])
        );
    }
}
