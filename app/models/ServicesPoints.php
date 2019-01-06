<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class ServicesPoints extends \Phalcon\Mvc\Model
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
    protected $point_id;

    /**
     * Method to set the value of field serviceId
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
     * Method to set the value of field pointId
     *
     * @param integer $pointid
     * @return $this
     */
    public function setPointId($pointid)
    {
        $this->point_id = $pointid;

        return $this;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Returns the value of field pointId
     *
     * @return integer
     */
    public function getPointId()
    {
        return $this->point_id;
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
                    "callback" => function ($servicePoint) {
                        $service = Services::findFirstByServiceId($servicePoint->getServiceId());

                        if ($service)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'point_id',
            new Callback(
                [
                    "message" => "Такая точка оказания услуг не существует или не связана с компанией услуги",
                    "callback" => function ($servicePoint) {
                        $point = TradePoints::findFirstByPointId($servicePoint->getPointId());
                        $service = Services::findFirstByServiceId($servicePoint->getServiceId());

                        $equals = ($point->accounts->getCompanyId() == null && $service->accounts->getCompanyId() == null &&
                            $point->accounts->getUserId() == $service->accounts->getUserId()) ||
                            ($point->accounts->getCompanyId() == $service->accounts->getCompanyId()
                                && $service->accounts->getCompanyId()!=null);

                        if ($point && $service && $equals)
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
        $this->setSource("servicesPoints");
        $this->belongsTo('point_id', 'App\Models\TradePoints', 'point_id', ['alias' => 'TradePoints']);
        $this->belongsTo('service_id', 'App\Models\Services', 'service_id', ['alias' => 'Services']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'servicesPoints';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ServicesPoints[]|ServicesPoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ServicesPoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($serviceId, $pointId)
    {
        return ServicesPoints::findFirst(['service_id = :serviceId: AND point_id = :pointId:',
            'bind' => ['serviceId' => $serviceId, 'pointId' => $pointId]]);
    }

    public function beforeDelete(){
        //Проверка, можно ли удалить связь с услугой (услуга обязательно должна быть связана с точкой оказания услуг или регионом)
        $service = Services::findFirstByServiceId($this->getServiceId());

        if($service->getRegionId() != null){
            return true;
        }

        $servicesPoints = ServicesPoints::findByServiceId($this->getServiceId());

        if(count($servicesPoints) > 1){
            return true;
        }
        return false;
    }

}
