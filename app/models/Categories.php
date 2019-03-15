<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class Categories extends CategoriesModel
{
    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();
        if($this->getParentId()!= null)
            $validator->add(
                'parent_id',
                new Callback(
                    [
                        "message" => "Родительская категория не существует",
                        "callback" => function ($category) {
                            $categoryParent = Categories::findFirstByCategoryId($category->getParentId());
                            if ($categoryParent)
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
        $this->setSource("categories");
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'categories';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Categories[]|Categories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }
    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Categories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findAllCategories(){
        return self::find(['category_id > 20','order' => 'parent_id DESC']);
    }

    public static function findCategoriesForSite(){
        $categories = Categories::find(['category_id > 20', 'order' => 'parent_id DESC']);

        $categories2 = [];
        foreach ($categories as $category) {
            if ($category->getParentId() == null) {
                $categories2[] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                    'description' => $category->getDescription(), 'img' => $category->getImg(),
                    'child' => []];
            } else {
                for ($i = 0; $i < count($categories2); $i++)
                    if ($categories2[$i]['id'] == $category->getParentId()) {
                        $categories2[$i]['child'][] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                            'description' => $category->getDescription(), 'img' => $category->getImg(),
                            'child' => [], 'check' => false];
                        break;
                    }
            }
        }

        return $categories2;
    }
}