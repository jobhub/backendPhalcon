<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class Services extends \Phalcon\Mvc\Model
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
    protected $companyId;

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
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deleted;

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
     * Method to set the value of field companyId
     *
     * @param integer $companyId
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

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
     * Method to set the value of field deleted
     *
     * @param string $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
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
     * Returns the value of field companyId
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->companyId;
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
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
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
            'companyId',
            new Callback(
                [
                    "message" => "Такая компания не существует",
                    "callback" => function ($service) {
                        $company = Companies::findFirstByCompanyId($service->getCompanyId());

                        if ($company)
                            return true;
                        return false;
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
        $this->belongsTo('companyId', '\Companies', 'companyId', ['alias' => 'Companies']);
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

    public function delete($delete = false, $data = null, $whiteList = null)
    {
        if (!$delete) {
            $this->setDeleted(true);
            return $this->save();
        } else {
            $result = parent::delete($data, $whiteList);
            return $result;
        }
    }

    public function restore()
    {
        $this->setDeleted(false);
        return $this->save();
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints[]|TradePoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null, $addParamNotDeleted = true)
    {

        if ($addParamNotDeleted) {
            $conditions = $parameters['conditions'];

            if (trim($conditions) != "") {
                $conditions .= ' AND deleted != true';
            }else{
                $conditions .= 'deleted != true';
            }
            $parameters['conditions'] = $conditions;
        }
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            $conditions = $parameters['conditions'];

            if (trim($conditions) != "") {
                $conditions .= ' AND deleted != true';
            }else{
                $conditions .= 'deleted != true';
            }
            $parameters['conditions'] = $conditions;
        }

        return parent::findFirst($parameters);
    }

}
