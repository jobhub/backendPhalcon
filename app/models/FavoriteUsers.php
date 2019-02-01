<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavoriteUsers extends FavouriteModel
{
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("favorite_users");
        $this->belongsTo('object_id', 'App\Models\Users', 'user_id', ['alias' => 'object']);
        $this->belongsTo('subject_id', 'App\Models\Accounts', 'id', ['alias' => 'subject']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favorite_users';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoriteusers[]|Favoriteusers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoriteusers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function handleFavourites($favs)
    {
        $handledFavs = [];
        foreach ($favs as $fav) {
            $handledFavs[] = self::handleFavourite($fav);
        }
        return $handledFavs;
    }

    public static function handleFavourite($fav)
    {
        $handledFavUser = [
            'subscriber' => Userinfo::findUserInfoById($fav['object_id'],Userinfo::shortColumnsInStr),
        ];
        return $handledFavUser;
    }
}
