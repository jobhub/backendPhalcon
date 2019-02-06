<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavoriteCompanies extends FavouriteModel
{

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("favorite_companies");
        $this->belongsTo('object_id', 'App\Models\Companies', 'company_id', ['alias' => 'object']);
        $this->belongsTo('subject_id', 'App\Models\Accounts', 'id', ['alias' => 'subject']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favorite_companies';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavoriteCompanies[]|FavoriteCompanies|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavoriteCompanies|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function handleSubscriptions($favs)
    {
        $handledFavs = [];
        foreach ($favs as $fav) {
            $handledFavs[] = self::handleSubscription($fav);
        }
        return $handledFavs;
    }



    public static function handleSubscription($fav)
    {
        $handledFavUser = [
            'subscription' => Companies::findCompanyById($fav['object_id'],Companies::shortColumnsInStr),
        ];
        return $handledFavUser;
    }
}
