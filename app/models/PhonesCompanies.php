<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

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
    protected $phone_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $company_id;

    /**
     * Method to set the value of field phoneId
     *
     * @param integer $phone_id
     * @return $this
     */
    public function setPhoneId($phone_id)
    {
        $this->phone_id = $phone_id;

        return $this;
    }

    /**
     * Method to set the value of field companyId
     *
     * @param integer $company_id
     * @return $this
     */
    public function setCompanyId($company_id)
    {
        $this->company_id = $company_id;

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
     * Returns the value of field companyId
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->company_id;
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
            'company_id',
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
        $this->belongsTo('company_id', 'App\Models\Companies', 'company_id', ['alias' => 'Companies']);
        $this->belongsTo('phone_id', 'App\Models\Phones', 'phone_id', ['alias' => 'Phones']);
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

    public static function findByIds($companyId, $phoneId)
    {
        return PhonesCompanies::findFirst(["company_id = :companyId: AND phone_id = :phoneId:",
            'bind' =>
                ['companyId' => $companyId,
                    'phoneId' => $phoneId
                ]]);
    }

    public static function getCompanyPhones($companyId)
    {
        $db = DI::getDefault()->getDb();

        $query = $db->prepare('SELECT p.phone FROM "phonesCompanies" p_c INNER JOIN phones p ON 
            (p_c.phone_id = p.phone_id) where p_c.company_id = :companyId'
        );

        $query->execute([
            'companyId' => $companyId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
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
