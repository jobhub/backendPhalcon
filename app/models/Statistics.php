<?php

namespace App\Models;

class Statistics extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $statistics_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $number_of_clicks;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $average_display_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $number_of_display;

    /**
     * Method to set the value of field statistics_id
     *
     * @param integer $statistics_id
     * @return $this
     */
    public function setStatisticsId($statistics_id)
    {
        $this->statistics_id = $statistics_id;

        return $this;
    }

    /**
     * Method to set the value of field number_of_clicks
     *
     * @param integer $number_of_clicks
     * @return $this
     */
    public function setNumberOfClicks($number_of_clicks)
    {
        $this->number_of_clicks = $number_of_clicks;

        return $this;
    }

    /**
     * Method to set the value of field average_view_time
     *
     * @param string $average_display_time
     * @return $this
     */
    public function setAverageDisplayTime($average_display_time)
    {
        $this->average_display_time = $average_display_time;

        return $this;
    }

    /**
     * Method to set the value of field number_of_view
     *
     * @param integer $number_of_display
     * @return $this
     */
    public function setNumberOfDisplay($number_of_display)
    {
        $this->number_of_display = $number_of_display;

        return $this;
    }

    /**
     * Returns the value of field statistics_id
     *
     * @return integer
     */
    public function getStatisticsId()
    {
        return $this->statistics_id;
    }

    /**
     * Returns the value of field number_of_clicks
     *
     * @return integer
     */
    public function getNumberOfClicks()
    {
        return $this->number_of_clicks;
    }

    /**
     * Returns the value of field average_view_time
     *
     * @return string
     */
    public function getAverageDisplayTime()
    {
        return $this->average_display_time;
    }

    /**
     * Returns the value of field number_of_view
     *
     * @return integer
     */
    public function getNumberOfDisplay()
    {
        return $this->number_of_display;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("statistics");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'statistics';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Statistics[]|Statistics|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Statistics|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findById(int $id, array $columns = null)
    {
        if ($columns == null)
            return self::findFirst(['statistics_id = :id:',
                'bind' => ['id' => $id]]);
        else {
            return self::findFirst(['columns' => $columns, 'statistics_id = :id:',
                'bind' => ['id' => $id]]);
        }
    }
}
