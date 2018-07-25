<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class Services extends NotDeletedModelWithCascade
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $serviceId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectId;

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
    protected $datePublication;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $priceMin;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $priceMax;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectType;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $regionId;

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $serviceId
     * @return $this
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Method to set the value of field subjectId
     *
     * @param integer $subjectId
     * @return $this
     */
    public function setSubjectId($subjectId)
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function setSubjectType($subjectType)
    {
        $this->subjectType = $subjectType;

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
     * @param string $datePublication
     * @return $this
     */
    public function setDatePublication($datePublication)
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    /**
     * Method to set the value of field priceMin
     *
     * @param integer $priceMin
     * @return $this
     */
    public function setPriceMin($priceMin)
    {
        $this->priceMin = $priceMin;

        return $this;
    }

    /**
     * Method to set the value of field priceMax
     *
     * @param integer $priceMax
     * @return $this
     */
    public function setPriceMax($priceMax)
    {
        $this->priceMax = $priceMax;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectType()
    {
        return $this->subjectType;
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
        return $this->datePublication;
    }

    /**
     * Returns the value of field priceMin
     *
     * @return integer
     */
    public function getPriceMin()
    {
        return $this->priceMin;
    }

    /**
     * Returns the value of field priceMax
     *
     * @return integer
     */
    public function getPriceMax()
    {
        return $this->priceMax;
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
            'subjectId',
            new Callback(
                [
                    "message" => "Такой субъект не существует",
                    "callback" => function ($service) {
                        return Subjects::checkSubjectExists($service->getSubjectId(), $service->getSubjectType());
                    }
                ]
            )
        );

        $validator->add(
            'priceMin',
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

        //под вопросом. Могут быть проблемы, если указывать id при создании
        //А так, если id уже есть, предполагается, что услуга ранее была создана
        if($this->getServiceId() != null)
        $validator->add(
            'regionId',
            new Callback(
                [
                    "message" => "Для услуги должна быть указана точка (точки) продаж или, хотя бы, регион",
                    "callback" => function ($service) {
                        if ($service->getRegionId() != null) {
                            $region = Regions::findFirstByRegionId($service->getRegionId());

                            if ($region)
                                return true;
                            return false;
                        }

                        $servicesPoints = ServicesPoints::findFirstByServiceId($service->getServiceId());
                        if ($servicesPoints)
                            return true;
                        return false;
                    }
                ]
            )
        );


        $validator->add(
            "datePublication",
            new PresenceOf(
                [
                    "message" => "Не указана дата опубликования услуги",
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
        //$this->setSchema("public");
        $this->setSource("services");
        $this->hasMany('serviceId', 'ServicesPoints', 'serviceId', ['alias' => 'ServicesPoints']);
        $this->belongsTo('regionId', '\Regions', 'regionId', ['alias' => 'Regions']);
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
