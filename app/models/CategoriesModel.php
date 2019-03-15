<?php

namespace App\Models;

class CategoriesModel extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $category_id;
    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=false)
     */
    protected $category_name;
    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $parent_id;
    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;
    /**
     *
     * @var string
     * @Column(type="string", length=260, nullable=true)
     */
    protected $img;
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
     * Method to set the value of field categoryName
     *
     * @param string $category_name
     * @return $this
     */
    public function setCategoryName($category_name)
    {
        $this->category_name = $category_name;
        return $this;
    }
    /**
     * Method to set the value of field parentId
     *
     * @param integer $parent_id
     * @return $this
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
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
     * Method to set the value of field img
     *
     * @param string $img
     * @return $this
     */
    public function setImg($img)
    {
        $this->img = $img;
        return $this;
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
     * Returns the value of field categoryName
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }
    /**
     * Returns the value of field parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parent_id;
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
     * Returns the value of field img
     *
     * @return string
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("categories_model");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'categories_model';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return CategoriesModel[]|CategoriesModel|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return CategoriesModel|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
