<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

class CompaniesCategories extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $company_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $category_id;

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
     * Method to set the value of field categoryId
     *
     * @param integer $category_id
     * @return $this
     */
    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;

        return $this;
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
     * Returns the value of field categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("companiesCategories");
        $this->belongsTo('category_id', 'App\Models\Categories', 'category_id', ['alias' => 'Categories']);
        $this->belongsTo('company_id', 'App\Models\Companies', 'company_id', ['alias' => 'Companies']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'companiesCategories';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return CompaniesCategories[]|CompaniesCategories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return CompaniesCategories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function getCategoriesByCompany($companyId)
    {
        $db = DI::getDefault()->getDb();

        $query = $db->prepare('SELECT c.* FROM categories c INNER JOIN "companiesCategories" c_c  
            USING(category_id) where c_c.company_id = :companyId'
        );

        $query->execute([
            'companyId' => $companyId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
