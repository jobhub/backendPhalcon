<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavouriteServices extends FavouriteModel
{

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("favourite_services");
        $this->belongsTo('subject_id', 'App\Models\Accounts', 'id', ['alias' => 'Accounts']);
        $this->belongsTo('object_id', 'App\Models\Services', 'service_id', ['alias' => 'Services']);
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

        return self::findFirst(['subject_id = ANY(:ids:) and object_id = :serviceId:',
            'bind' => ['ids' => $account->getRelatedAccounts(),'serviceId'=>$serviceId]
        ]);
    }

    public static function findFavourites($accountId, $page = 1, $page_size = Services::DEFAULT_RESULT_PER_PAGE){
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $modelsManager = DI::getDefault()->get('modelsManager');

        $account = Accounts::findFirstById($accountId);

        $columns = [];
        foreach (Services::publicColumns as $publicColumn) {
            $columns[] = 's.' . $publicColumn;
        }

        $result = $modelsManager->createBuilder()
            ->columns($columns)
            ->from(["s" => "App\Models\Services"])
            ->join('App\Models\FavouriteServices','fav_serv.object_id = s.service_id','fav_serv')
            ->where('fav_serv.subject_id = ANY(:ids:) and s.deleted = false',
                ['ids' => $account->getRelatedAccounts()])
            ->orderBy('fav_serv.favourite_date desc')
            ->limit($page_size)
            ->offset($offset)
            ->getQuery()
            ->execute();

        return Services::handleServiceFromArray($result->toArray());
    }
}
