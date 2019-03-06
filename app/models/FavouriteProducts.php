<?php

namespace App\Models;

use App\Libs\SupportClass;

use Phalcon\DI\FactoryDefault as DI;

class FavouriteProducts extends FavouriteModel
{

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("favourite_products");
        $this->belongsTo('object_id', 'App\Models\Products', 'product_id', ['alias' => 'Products']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favourite_products';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavouriteProducts[]|FavouriteProducts|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavouriteProducts|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findFavourites($accountId, $page = 1, $page_size = Services::DEFAULT_RESULT_PER_PAGE){
        /*$page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;*/
        $modelsManager = DI::getDefault()->get('modelsManager');

        $account = Accounts::findFirstById($accountId);

        $columns = [];
        foreach (Products::shortColumns as $shortColumn) {
            $columns[] = 'p.' . $shortColumn;
        }

        $result = $modelsManager->createBuilder()
            ->columns($columns)
            ->from(["p" => "App\Models\Products"])
            ->join('App\Models\FavouriteProducts','fav_prod.object_id = p.product_id','fav_prod')
            ->where('fav_prod.subject_id = ANY(:ids:) and p.deleted = false',
                ['ids' => $account->getRelatedAccounts()])
            ->orderBy('fav_prod.favourite_date desc');


        $result = SupportClass::executeWithPagination($result,
            ['ids' => $account->getRelatedAccounts()],$page,$page_size);

        $result['data'] = Products::handleShortInfoProductFromArray($result['data']);

        return $result;
    }

}
