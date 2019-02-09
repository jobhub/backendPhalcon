<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavoriteCategories extends FavouriteModel
{
    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=true)
     */
    protected $radius;

    /**
     * Method to set the value of field radius
     *
     * @param string $radius
     * @return $this
     */
    public function setRadius($radius)
    {
        $this->radius = $radius;

        return $this;
    }

    /**
     * Returns the value of field radius
     *
     * @return string
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {

        return parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("favorite_categories");
        $this->belongsTo('category_id', 'App\Models\Categories', 'category_id', ['alias' => 'Categories']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favorite_categories';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavoriteCategories[]|FavoriteCategories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavoriteCategories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findForUser($accountId,$page = 1,$page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        return FavoriteCategories::findFavourites($accountId,$page,$page_size);
    }
}
