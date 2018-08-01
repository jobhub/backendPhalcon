<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class Services extends SubjectsWithNotDeleted
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $serviceid;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $datepublication;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $pricemin;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $pricemax;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $regionid;

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $serviceid
     * @return $this
     */
    public function setServiceId($serviceid)
    {
        $this->serviceid = $serviceid;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field datePublication
     *
     * @param string $datepublication
     * @return $this
     */
    public function setDatePublication($datepublication)
    {
        $this->datepublication = $datepublication;

        return $this;
    }

    /**
     * Method to set the value of field priceMin
     *
     * @param integer $pricemin
     * @return $this
     */
    public function setPriceMin($pricemin)
    {
        $this->pricemin = $pricemin;

        return $this;
    }

    /**
     * Method to set the value of field priceMax
     *
     * @param integer $pricemax
     * @return $this
     */
    public function setPriceMax($pricemax)
    {
        $this->pricemax = $pricemax;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $regionid
     * @return $this
     */
    public function setRegionId($regionid)
    {
        $this->regionid = $regionid;

        return $this;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionid;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceid;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field datePublication
     *
     * @return string
     */
    public function getDatePublication()
    {
        return $this->datepublication;
    }

    /**
     * Returns the value of field priceMin
     *
     * @return integer
     */
    public function getPriceMin()
    {
        return $this->pricemin;
    }

    /**
     * Returns the value of field priceMax
     *
     * @return integer
     */
    public function getPriceMax()
    {
        return $this->pricemax;
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
            'pricemin',
            new Callback(
                [
                    "message" => "Минимальная цена должна быть меньше (или равна) максимальной",
                    "callback" => function ($service) {
                        if ($service->getPriceMin() <= $service->getPriceMax())
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'regionid',
            new Callback(
                [
                    "message" => "Для услуги должен быть указан регион",
                    "callback" => function ($service) {
                        $region = Regions::findFirstByRegionid($service->getRegionId());

                        if ($region)
                            return true;
                        return false;
                    }
                ]
            )
        );


        $validator->add(
            "datepublication",
            new PresenceOf(
                [
                    "message" => "Не указана дата опубликования услуги",
                ]
            )
        );

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("services");
        $this->hasMany('serviceid', 'ServicesPoints', 'serviceid', ['alias' => 'ServicesPoints']);
        $this->belongsTo('regionid', '\Regions', 'regionid', ['alias' => 'Regions']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'services';
    }

}
