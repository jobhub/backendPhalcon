<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

abstract class InvitesFromCompaniesModel extends InvitesModel
{

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'who_invited',
            new Callback(
                [
                    "message" => "Приглашающий аккаунт не существует",
                    "callback" => function ($invite) {
                        return $invite->whoInvited?true:false;
                    }
                ]
            )
        );
        $validator->add(
            'where_invited',
            new Callback(
                [
                    "message" => "Приглашающая компания не существует",
                    "callback" => function ($invite) {
                        return $invite->whereInvited?true:false;
                    }
                ]
            )
        );

        return parent::validation()&&$this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->belongsTo('where_invited', 'App\Models\Companies', 'company_id', ['alias' => 'WhereInvited']);
        $this->belongsTo('who_invited', 'App\Models\Accounts', 'id', ['alias' => 'WhoInvited']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return InvitesRegisterToBeManager[]|InvitesRegisterToBeManager|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return InvitesRegisterToBeManager|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
