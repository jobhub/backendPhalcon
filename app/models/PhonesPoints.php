<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class PhonesPoints extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $phone_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $point_id;

    /**
     * Method to set the value of field phoneId
     *
     * @param integer $phoneid
     * @return $this
     */
    public function setPhoneId($phoneid)
    {
        $this->phone_id = $phoneid;

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
     * Returns the value of field phoneId
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phone_id;
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
            'phone_id',
            new Callback(
                [
                    "message" => "Телефон не был создан",
                    "callback" => function ($phoneCompany) {
                        $phone = Phones::findFirstByPhoneId($phoneCompany->getPhoneId());

                        if ($phone)
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
                    "message" => "Такая точка оказания услуг не существует",
                    "callback" => function ($phonePoint) {
                        $point = TradePoints::findFirstByPointId($phonePoint->getPointId());

                        if ($point)
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
        $this->setSource("phonesPoints");
        $this->belongsTo('phone_id', 'App\Models\Phones', 'phone_id', ['alias' => 'Phones']);
        $this->belongsTo('point_id', 'App\Models\TradePoints', 'point_id', ['alias' => 'TradePoints']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'phonesPoints';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesPoints[]|PhonesPoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesPoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($pointId, $phoneId)
    {
        return PhonesPoints::findFirst(["point_id = :pointId: AND phone_id = :phoneId:",
            'bind' =>
                ['pointId' => $pointId,
                    'phoneId' => $phoneId
                ]]);
    }

    public static function findPhonesForPoint($pointId)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');

        $result = $modelsManager->createBuilder()
            ->from(["p" => "App\Models\Phones"])
            ->join('App\Models\PhonesPoints', 'p.phone_id = pp.phone_id', 'pp')
            ->join('App\Models\TradePoints', 'pp.point_id = tp.point_id', 'tp')
            ->where('tp.point_id = :pointId:', ['pointId' => $pointId])
            ->getQuery()
            ->execute();

        if (count($result) == 0) {
            $point = TradePoints::findFirstByPointId($pointId);
            if ($point->accounts->getCompanyId() != null) {

                $result = $modelsManager->createBuilder()
                    ->from(["p" => "App\Models\Phones"])
                    ->join('App\Models\PhonesCompanies', 'p.phone_id = pc.phone_id', 'pc')
                    ->join('App\Models\Companies', 'pc.company_id = c.company_id', 'c')
                    ->where('c.company_id = :companyId:', ['companyId' => $point->accounts->getCompanyId()])
                    ->getQuery()
                    ->execute();
            } else {
                $result = $modelsManager->createBuilder()
                    ->from(["p" => "App\Models\Phones"])
                    ->join('App\Models\Users', 'p.phone_id = u.phone_id', 'u')
                    ->where('u.user_id = :userId:', ['userId' => $point->accounts->getUserId()])
                    ->getQuery()
                    ->execute();
            }
        }

        return $result;
    }
}
