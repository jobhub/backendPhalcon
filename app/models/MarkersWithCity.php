<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class MarkersWithCity extends Markers
{

     /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $city_id;

    /**
     * Method to set the value of field city_id
     *
     * @param integer $city_id
     * @return $this
     */
    public function setCityId($city_id)
    {
        $this->city_id = $city_id;

        return $this;
    }

    /**
     * Returns the value of field city_id
     *
     * @return integer
     */
    public function getCityId()
    {
        return $this->city_id;
    }

    public function validation()
    {
        $validator = new Validation();

        if($this->getCityId()!=null) {
            $validator->add(
                'city_id',
                new Callback([
                    "message" => "Указанный город не найден",
                    'callback' => function ($marker) {
                        return $marker->cities?true:false;
                    }
                ])
            );
        }

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("markers_with_city");
        $this->belongsTo('city_id', 'App\Models\Cities', 'city_id', ['alias' => 'Cities']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'markers_with_city';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MarkersWithCity[]|MarkersWithCity|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MarkersWithCity|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
