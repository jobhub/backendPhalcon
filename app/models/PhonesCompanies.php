<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
class PhonesCompanies extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $phoneId;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $companyId;

    /**
     * Method to set the value of field phoneId
     *
     * @param integer $phoneId
     * @return $this
     */
    public function setPhoneId($phoneId)
    {
        $this->phoneId = $phoneId;

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
     * Returns the value of field phoneId
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phoneId;
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
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'phoneId',
            new Callback(
                [
                    "message" => "Телефон не был создан",
                    "callback" => function($phoneCompany) {
                        $phone = Phones::findFirstByPhoneId($phoneCompany->getPhoneId());

                        if($phone)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'companyId',
            new Callback(
                [
                    "message" => "Такая компания не существует",
                    "callback" => function($phoneCompany) {
                        $phone = Companies::findFirstByCompanyId($phoneCompany->getCompanyId());

                        if($phone)
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
        //$this->setSchema("job");
        $this->setSource("phonesCompanies");
        $this->belongsTo('companyId', '\Companies', 'companyId', ['alias' => 'Companies']);
        $this->belongsTo('phoneId', '\Phones', 'phoneId', ['alias' => 'Phones']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesCompanies[]|PhonesCompanies|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesCompanies|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'phonesCompanies';
    }

}
