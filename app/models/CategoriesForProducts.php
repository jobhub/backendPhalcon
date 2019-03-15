<?php

namespace App\Models;

class CategoriesForProducts extends CategoriesModel
{

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("categories_for_products");
        $this->hasMany('category_id', 'App\Models\CategoriesForProducts', 'parent_id', ['alias' => 'CategoriesForProducts']);
        $this->belongsTo('parent_id', 'App\Models\CategoriesForProducts', 'category_id', ['alias' => 'CategoriesForProducts']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'categories_for_products';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return CategoriesForProducts[]|CategoriesForProducts|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return CategoriesForProducts|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findAllCategories(){
        return self::find(['order' => 'parent_id DESC']);
    }
}
