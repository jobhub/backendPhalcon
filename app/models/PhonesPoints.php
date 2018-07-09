<?php

class PhonesPoints extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $phoneId;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $pointId;

    /**
     * Method to set the value of field phoneId
     *
     * @param integer $phoneId
     * @return $this
     */
    public function setPhoneId($phoneId)
    {
        $this->phoneId = $phoneId;

        return $this;
    }

    /**
     * Method to set the value of field pointId
     *
     * @param integer $pointId
     * @return $this
     */
    public function setPointId($pointId)
    {
        $this->pointId = $pointId;

        return $this;
    }

    /**
     * Returns the value of field phoneId
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phoneId;
    }

    /**
     * Returns the value of field pointId
     *
     * @return integer
     */
    public function getPointId()
    {
        return $this->pointId;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("job");
        $this->setSource("phonesPoints");
        $this->belongsTo('phoneId', '\Phones', 'phoneId', ['alias' => 'Phones']);
        $this->belongsTo('pointId', '\TradePoints', 'pointId', ['alias' => 'TradePoints']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'phonesPoints';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesPoints[]|PhonesPoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesPoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
