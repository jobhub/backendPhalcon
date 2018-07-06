<?php

class Regions extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $regionId;

    /**
     *
     * @var string
     * @Column(type="string", length=70, nullable=false)
     */
    protected $regionName;

    /**
     * Method to set the value of field regionId
     *
     * @param integer $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * Method to set the value of field regionName
     *
     * @param string $regionName
     * @return $this
     */
    public function setRegionName($regionName)
    {
        $this->regionName = $regionName;

        return $this;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * Returns the value of field regionName
     *
     * @return string
     */
    public function getRegionName()
    {
        return $this->regionName;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("job");
        $this->setSource("regions");
        $this->hasMany('regionId', 'Companies', 'regionId', ['alias' => 'Companies']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'regions';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Regions[]|Regions|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Regions|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
