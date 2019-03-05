<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Alpha as AlphaValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Alnum as AlnumValidator;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Validation\Validator\Regex;

class Products extends AccountWithNotDeletedWithCascade
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $product_id;

    /**
     *
     * @var string
     * @Column(type="string", length=65, nullable=false)
     */
    protected $product_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $phone_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $show_company_place;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $category_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $date_creation;

    /**
     * @return string
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * @param string $date_creation
     */
    public function setDateCreation($date_creation)
    {
        $this->date_creation = $date_creation;
    }
    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * @param int $category_id
     */
    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
    }

    /**
     * Method to set the value of field product_id
     *
     * @param integer $product_id
     * @return $this
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;

        return $this;
    }

    /**
     * Method to set the value of field product_name
     *
     * @param string $product_name
     * @return $this
     */
    public function setProductName($product_name)
    {
        $this->product_name = $product_name;

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
     * Method to set the value of field price
     *
     * @param integer $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Method to set the value of field phone_id
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
     * Method to set the value of field show_company_place
     *
     * @param string $show_company_place
     * @return $this
     */
    public function setShowCompanyPlace($show_company_place)
    {
        $this->show_company_place = $show_company_place;

        return $this;
    }

    /**
     * Returns the value of field product_id
     *
     * @return integer
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * Returns the value of field product_name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->product_name;
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
     * Returns the value of field price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the value of field phone_id
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phone_id;
    }

    /**
     * Returns the value of field show_company_place
     *
     * @return string
     */
    public function getShowCompanyPlace()
    {
        return $this->show_company_place;
    }

    public function validation()
    {
        $validator = new Validation();

        if ($this->getPhoneId() != null) {
            $validator->add(
                'phone_id',
                new Callback(
                    [
                        "message" => "Phone does not exist",
                        "callback" => function ($product) {
                            $phone = Phones::findFirstByPhoneId($product->getPhoneId());

                            if ($phone)
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        $validator->add(
            'category_id',
            new Callback(
                [
                    "message" => "Category does not exist",
                    "callback" => function ($product) {
                        $category = Categories::findFirstByCategoryId($product->getCategoryId());

                        if ($category)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'product_name',
            new PresenceOf(
                [
                    "message" => ":field: must be",
                ]
            )
        );

        $validator->add(
            'price',
            new PresenceOf(
                [
                    "message" => ":field must be fill",
                ]
            )
        );

        $validator->add(
            'product_name',
            new Regex(
                [
                    "pattern" => "/^[а-яА-Яa-zA-Z0-9](?:_?[а-яА-Яa-zA-Z0-9 ])*$/",
                    "message" => "product_name must contain only a-z, A-Z, , _,0-9",
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
        $this->setSource("products");
        $this->hasMany('product_id', 'App\Models\TagsProducts', 'object_id', ['alias' => 'TagsProducts']);
        $this->belongsTo('phone_id', 'App\Models\Phones', 'phone_id', ['alias' => 'Phones']);
        $this->belongsTo('category_id', 'App\Models\Categories', 'category_id', ['alias' => 'Categories']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'products';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Products[]|Products|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Products|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findProductById($productId){
        return self::findFirstByProductId($productId);
    }
}
