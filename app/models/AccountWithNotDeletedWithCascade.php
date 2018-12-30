<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class AccountWithNotDeletedWithCascade extends NotDeletedModelWithCascade
{
    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $account_id;

    public function setAccountId($accountId)
    {
        $this->account_id = $accountId;

        return $this;
    }

    public function getAccountId()
    {
        return $this->account_id;
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
            '$account_id',
            new Callback(
                [
                    "message" => "Такой аккаунт не существует",
                    "callback" => function ($account_model) {
                        return Accounts::findFirstById($account_model->getAccountId())?true:false;
                    }
                ]
            )
        );
        return $this->validate($validator);
    }

    public function initialize(){
        $this->belongsTo('account_id', '\Accounts', 'id', ['alias' => 'accounts']);
    }

    public static function findByAccount($accountId, $order = null, $columns = null)
    {
        if($columns == null)
            return parent::find(['account_id = :accountId:',
                'bind' => ['accountId' => $accountId],
                'order' => $order]);
        else{
            return parent::find(['columns' => $columns,'account_id = :accountId:',
                'bind' => ['accountId' => $accountId],
                'order' => $order]);
        }
    }
}