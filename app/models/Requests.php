<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Requests extends \App\Models\AccountWithNotDeletedWithCascade
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $request_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $service_id;

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
    protected $date_end;

    /**
     *
     * @var integer
     * @Column(type="integer", nullable=false)
     */
    protected $status;

    const publicColumns = ['request_id', 'service_id', 'description', 'date_end', 'status'];

    /**
     * Method to set the value of field requestId
     *
     * @param integer $requestid
     * @return $this
     */
    public function setRequestId($requestid)
    {
        $this->request_id = $requestid;

        return $this;
    }

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $serviceid
     * @return $this
     */
    public function setServiceId($serviceid)
    {
        $this->service_id = $serviceid;

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
     * @param string $dateend
     * @return $this
     */
    public function setDateEnd($dateend)
    {
        $this->date_end = $dateend;

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
     * Returns the value of field requestId
     *
     * @return integer
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->service_id;
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
        return $this->date_end;
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
            'service_id',
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
                "message" => "Поле статус имеет неверное значение.",
                'callback' => function ($request) {
                    $status = Statuses::findFirstByStatusId($request->getStatus());
                    if (!$status)
                        return false;
                    return true;
                }
            ])
        );

        if($this->getDateEnd()!= null)
        $validator->add(
            'date_end',
            new Callback([
                "message" => "Крайняя дата на получение услуги должна быть позже текущего времени",
                'callback' => function ($request) {
                    if (strtotime($request->getDateEnd()) > strtotime(date('Y-m-d H:i:s')))
                        return true;
                    return false;
                }
            ])
        );

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        parent::initialize();
        $this->setSource("requests");
        $this->belongsTo('service_id', 'App\Models\Services', 'service_id', ['alias' => 'Services']);
        $this->belongsTo('status', 'App\Models\Statuses', 'status_id', ['alias' => 'Statuses']);
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

    public function getSequenceName()
    {
        return "request_requestid_seq";
    }

    public static function findRequestByCompany($companyId)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');
        $result = $modelsManager->createBuilder()
            ->columns(self::publicColumns)
            ->from(["n" => "App\Models\Requests"])
            ->join('App\Models\Accounts', 'n.account_id = a.id', 'a')
            ->where('a.company_id = :companyId: and n.deleted = false', ['companyId' => $companyId])
            ->getQuery()
            ->execute();

        return self::handleRequestFromArray($result->toArray());
    }

    public static function findRequestByUser($userId)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');
        $result = $modelsManager->createBuilder()
            ->columns(self::publicColumns)
            ->from(["n" => "App\Models\Requests"])
            ->join('App\Models\Accounts', 'n.account_id = a.id and a.company_id is null', 'a')
            ->where('a.user_id = :userId: and n.deleted = false', ['userId' => $userId])
            ->getQuery()
            ->execute();

        return self::handleRequestFromArray($result->toArray());
    }

    private static function handleRequestFromArray(array $requests)
    {
        return $requests;
    }
}
