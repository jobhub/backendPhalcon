<?php

namespace App\Models;

use App\Libs\GeoPosition;
use App\Libs\TileSystem;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class Markers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $marker_id;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $longitude;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $latitude;

    /**
     *
     * @var integer
     * @Column(type="integer", nullable=true)
     */
    protected $quadkey;

    /**
     * Method to set the value of field marker_id
     *
     * @param integer $marker_id
     * @return $this
     */
    public function setMarkerId($marker_id)
    {
        $this->marker_id = $marker_id;

        return $this;
    }

    /**
     * Method to set the value of field longitude
     *
     * @param string $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Method to set the value of field latitude
     *
     * @param string $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Method to set the value of field quedkey
     *
     * @param integer $quadkey
     * @return $this
     */
    public function setQuadkey($quadkey)
    {
        $this->quadkey = $quadkey;

        return $this;
    }

    /**
     * Returns the value of field marker_id
     *
     * @return integer
     */
    public function getMarkerId()
    {
        return $this->marker_id;
    }

    /**
     * Returns the value of field longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Returns the value of field latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Returns the value of field quedkey
     *
     * @return integer
     */
    public function getQuadkey()
    {
        return $this->quadkey;
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'latitude',
            new Callback([
                "message" => "Не указана широта или указана неверно",
                'callback' => function ($task) {
                    if (GeoPosition::checkLatitude($task->getLatitude()))
                        return false;
                    return true;
                }
            ])
        );

        $validator->add(
            'longitude',
            new Callback([
                "message" => "Не указана долгота или указана неверно",
                'callback' => function ($task) {
                    if (GeoPosition::checkLongitude($task->getLongitude()))
                        return false;
                    return true;
                }
            ])
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("markers");
        $this->hasMany('marker_id', 'App\Models\TradePoints', 'marker_id', ['alias' => 'TradePoints']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'markers';
    }

    public function getSequenceName()
    {
        return "markers_marker_id_seq";
    }

    public function beforeSave()
    {
        $this->setQuadkey(TileSystem::latLongToQuadKeyDec($this->getLatitude(), $this->getLongitude(), TileSystem::MaxZoom));
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Markers[]|Markers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Markers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findById(int $id, array $columns = null)
    {
        if ($columns == null)
            return self::findFirst(['marker_id = :id:',
                'bind' => ['id' => $id]]);
        else {
            return self::findFirst(['columns' => $columns, 'marker_id = :id:',
                'bind' => ['id' => $id]]);
        }
    }
}
