<?php

class UsersCategories extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $categoryid;

    /**
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserId($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Method to set the value of field categoryid
     *
     * @param integer $categoryid
     * @return $this
     */
    public function setCategoryId($categoryid)
    {
        $this->categoryid = $categoryid;

        return $this;
    }

    /**
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userid;
    }

    /**
     * Returns the value of field categoryid
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryid;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("userscategories");
        $this->belongsTo('categoryid', '\Categories', 'categoryid', ['alias' => 'Categories']);
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'UsersCategories';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UsersCategories[]|UsersCategories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UsersCategories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
