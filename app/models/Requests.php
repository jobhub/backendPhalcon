<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Requests extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $requestId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $serviceId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $dateEnd;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deleted;

    /**
     *
     * @var integer
     * @Column(type="integer", nullable=false)
     */
    protected $status;

    /**
     * Method to set the value of field requestId
     *
     * @param integer $requestId
     * @return $this
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * Method to set the value of field userId
     *
     * @param integer $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $serviceId
     * @return $this
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

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
     * Method to set the value of field dateEnd
     *
     * @param string $dateEnd
     * @return $this
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Method to set the value of field deleted
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Method to set the value of field deleted
     *
     * @param string $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Returns the value of field requestId
     *
     * @return integer
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceId;
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
     * Returns the value of field dateEnd
     *
     * @return string
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Returns the value of field deleted
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'userId',
            new Callback(
                [
                    "message" => "Такой пользователь не существует",
                    "callback" => function ($request) {
                        $user = Users::findFirstByUserId($request->getUserId());

                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'serviceId',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($request) {
                        $service = Services::findFirstByServiceId($request->getServiceId());

                        if ($service)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'status',
            new Callback([
                "message" => "Поле статус имеет неверное значение. Должно быть одно из следующих:\n0 - запрос в рассмотрении;\n
                1 - запрос выполняется;\n, 2 - запрос отклонен;\n, 3 - запрос выполнен;\n 4 - запрос не выполнен.",
                'callback' => function ($request) {
                    if($request->getStatus() < 0 || $request->getStatus() > 4)
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
        //$this->setSchema("public");
        $this->setSource("requests");
        $this->belongsTo('serviceId', '\Services', 'serviceId', ['alias' => 'Services']);
        $this->belongsTo('userId', '\Users', 'userId', ['alias' => 'Users']);
        $this->belongsTo('status', '\Statuses', 'statusId', ['alias' => 'Statuses']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'requests';
    }

    public function delete($delete = false, $data = null, $whiteList = null)
    {
        if (!$delete) {
            $this->setDeleted(true);
            return $this->save();
        } else {
            $result = parent::delete($data, $whiteList);
            return $result;
        }
    }

    public function restore()
    {
        $this->setDeleted(false);
        return $this->save();
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints[]|TradePoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null, $addParamNotDeleted = true)
    {

        if ($addParamNotDeleted) {
            $conditions = $parameters['conditions'];

            if (trim($conditions) != "") {
                $conditions .= ' AND deleted != true';
            }else{
                $conditions .= 'deleted != true';
            }
            $parameters['conditions'] = $conditions;
        }
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null, $addParamNotDeleted = true)
    {

        if ($addParamNotDeleted) {
            $conditions = $parameters['conditions'];

            if (trim($conditions) != "") {
                $conditions .= ' AND deleted != true';
            } else{
                $conditions .= 'deleted != true';
            }
            $parameters['conditions'] = $conditions;
        }

        return parent::findFirst($parameters);
    }

}
