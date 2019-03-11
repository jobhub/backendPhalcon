<?php

namespace App\Models;

use App\Controllers\AbstractController;
use App\Services\FavouriteService;
use App\Services\ImageService;
use Phalcon\DI\FactoryDefault as DI;

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

    const publicColumns = ['product_id', 'description', 'date_publication', 'price', 'product_name', 'account_id', 'phone_id',
        'show_company_place', 'category_id'];

    const shortColumns = ['product_id', 'product_name', 'price'];

    const DEFAULT_PRODUCT_IMAGE = 'images/no_image.jpg';

    const DEFAULT_RESULT_PER_PAGE = 10;

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
                    "pattern" => "/^[А-пр-эa-zA-Z0-9](?:_?[А-пр-эa-zA-Z0-9 ,.])*$/",
                    "message" => "product_name must contain only letters, numeric and space",
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

    public static function getIdField(){
        return 'product_id';
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

    public static function findProductById($productId)
    {
        return self::findFirstByProductId($productId);
    }

    /**
     * Function for handle information about products for favourite products.
     *
     * @param array $products
     * @param null $accountId
     * @return array
     */
    public static function handleShortInfoProductFromArray(array $products, $accountId = null)
    {
        $productsAll = [];

        foreach ($products as $product) {
            $productAll = [];

            $productAll['product_name'] = $product['product_name'];
            $productAll['product_id'] = $product['product_id'];
            $productAll['price'] = $product['price'];
            $productAll['category_id'] = $product['category_id'];

            $account = Accounts::findFirstById($product['account_id']);

            if ($account) {
                $productAll['publisher_company'] = $account->getUserInformation();
            }

            $images = ImagesProducts::findImages('App\Models\ImagesProducts', $productAll['product_id'], 1, 1);
            if (count($images['data']) > 0)
                $productAll['image'] = $images['data'][0];
            else
                $productAll['image'] = self::DEFAULT_PRODUCT_IMAGE;

            $productsAll[] = $productAll;
        }
        return $productsAll;
    }

    /**
     * Function for handle complete information about products. To show all information about product.
     *
     * @param array $product
     * @param null $accountId
     * @return array
     */
    public static function handleProductFromArray(array $product, $accountId = null)
    {
        if ($accountId == null) {
            $accountId = AbstractController::getAccountId();
        }

        $productAll = [];

        $productAll['product_name'] = $product['product_name'];
        $productAll['description'] = $product['description'];
        $productAll['product_id'] = $product['product_id'];
        $productAll['price'] = $product['price'];

        if ($product['show_company_place']) {

            $account = Accounts::findFirstByAccountId($productAll['account_id']);

            if ($account->getCompanyId() != null) {
                //$company = Companies::findCompanyById($account->getCompanyId());
                $points = TradePoints::findPointsByCompany($account->getCompanyId());

                if (count($points) > 0) {
                    $marker = $points[0]->markers;

                    $productAll['address'] = $points[0]['address'];
                    $productAll['longitude'] = $marker->getLongitude();
                    $productAll['latitude'] = $marker->getLatitude();
                }
            }
        }

        $di = DI::getDefault();

        $productAll['images'] = ImagesModel::findAllImages($di->getImageService()->getModelByType(ImageService::TYPE_PRODUCT),
            $productAll['product_id']);

        if($product['phone_id']!=null)
            $productAll['phone'] = Phones::findPhoneById($product['phone_id']);



        $account = Accounts::findFirstById($product['account_id']);

        if ($account) {
            $productAll['publisher_company'] = $account->getUserInformation();
        }

        $productAll['signed'] = FavouriteProducts::findByIds($di->getFavouriteService()->getModelByType(
            FavouriteService::TYPE_PRODUCT),$accountId,$productAll['product_id'])?true:false;

        return $productAll;
    }
}
