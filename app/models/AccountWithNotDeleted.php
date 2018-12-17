<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class AccountWithNotDeleted extends NotDeletedModel
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
                        return Accounts::findFirstByAccountId($account_model->getAccountId());
                    }
                ]
            )
        );
        return $this->validate($validator);
    }

    public function initialize(){
        $this->belongsTo('account_id', '\Accounts', 'id', ['alias' => 'accounts']);
    }
}