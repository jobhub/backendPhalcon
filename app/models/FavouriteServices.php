<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavouriteServices extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $account_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $service_id;

    protected $favourite_date;

    /**
     * @return mixed
     */
    public function getFavouriteDate()
    {
        return $this->favourite_date;
    }

    /**
     * @param mixed $favourite_date
     */
    public function setFavouriteDate($favourite_date)
    {
        $this->favourite_date = $favourite_date;
    }

    /**
     * Method to set the value of field account_id
     *
     * @param integer $account_id
     * @return $this
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;

        return $this;
    }

    /**
     * Method to set the value of field service_id
     *
     * @param integer $service_id
     * @return $this
     */
    public function setServiceId($service_id)
    {
        $this->service_id = $service_id;

        return $this;
    }

    /**
     * Returns the value of field account_id
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Returns the value of field service_id
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'account_id',
            new Callback(
                [
                    "message" => "Такой аккаунт не существует",
                    "callback" => function ($account_model) {
                        $account_exists = Accounts::findFirstById($account_model->getAccountId()) ? true : false;
                        if ($account_exists) {
                            if ($account_model->accounts->getCompanyId() != null) {
                                $accounts = Accounts::findByCompanyId($account_model->accounts->getCompanyId());

                                $ids = [];
                                foreach ($accounts as $account) {
                                    $ids[] = $account->getId();
                                }

                                $exists = self::findFirst(['account_id = ANY(:ids:)', 'bind' => ['ids' => $ids]]);

                                return $exists ? false : true;
                            }
                            return true;
                        }
                        return false;
                    }])
        );

        if($this->getFavouriteDate()!=null)
        $validator->add(
            'favourite_date',
            new Callback(
                [
                    "message" => "Время репоста должно быть раньше текущего",
                    "callback" => function ($forward) {
                        if (strtotime($forward->getFavouriteDate()) <= time())
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
        $this->setSource("favourite_services");
        $this->belongsTo('account_id', 'App\Models\Accounts', 'id', ['alias' => 'Accounts']);
        $this->belongsTo('service_id', 'App\Models\Services', 'service_id', ['alias' => 'Services']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favourite_services';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavouriteServices[]|FavouriteServices|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavouriteServices|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findFavouriteByIds($accountId, $serviceId)
    {
        $account = Accounts::findFirstById($accountId);
        if ($account->getCompanyId() != null) {
            $accounts = Accounts::findByCompanyId($account->getCompanyId());

            $ids = [];
            foreach ($accounts as $account) {
                $ids[] = $account->getId();
            }

            $fav= self::findFirst(['account_id = ANY(:ids:) and service_id = :serviceId:',
                'bind' => ['ids' => $ids,'serviceId'=>$serviceId]
            ]);

            return $fav;
        }
        return self::findFirst(['account_id = :accountId: and service_id = :serviceId:',
            'bind' => ['accountId' => $accountId,'serviceId'=>$serviceId]
        ]);
    }

    public static function findFavouritesByAccountId($accountId, $page = 1, $page_size = Services::DEFAULT_RESULT_PER_PAGE){
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $modelsManager = DI::getDefault()->get('modelsManager');

        $account = Accounts::findFirstById($accountId);
        if ($account->getCompanyId() != null) {
            $accounts = Accounts::findByCompanyId($account->getCompanyId());

            $ids = [];
            foreach ($accounts as $account) {
                $ids[] = $account->getId();
            }

            $where = 'fav_serv.account_id = ANY(:ids:)';
        } else{
            $ids = $accountId;
            $where = 'fav_serv.account_id = :ids:';
        }

        $columns = [];
        foreach (Services::publicColumns as $publicColumn) {
            $columns[] = 's.' . $publicColumn;
        }

        $result = $modelsManager->createBuilder()
            ->columns($columns)
            ->from(["s" => "App\Models\Services"])
            ->join('App\Models\FavouriteServices','fav_serv.service_id = s.service_id','fav_serv')
            ->where($where.' and s.deleted = false', ['ids' => $ids])
            ->orderBy('fav_serv.favourite_date desc')
            ->limit($page_size)
            ->offset($offset)
            ->getQuery()
            ->execute();

        return Services::handleServiceFromArray($result->toArray());
    }
}
