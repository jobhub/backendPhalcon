<?php

namespace App\Models;

class ForwardsNews extends ForwardsInNewsModel
{

    public function validation()
    {
        $validator = new Validation();

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSource("forwards_news");
        $this->belongsTo('object_id', 'App\Models\News', 'news_id', ['alias' => 'News']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'forwards_news';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ForwardsNews[]|ForwardsNews|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ForwardsNews|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findForwardByIds($accountId, $objectId)
    {
        return self::findFirst(['account_id = :accountId: and object_id = :objectId:','bind'=>
        [
            'accountId'=>$accountId,
            'objectId'=>$objectId
        ]])->toArray();
    }
}
