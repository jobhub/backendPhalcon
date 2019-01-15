<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

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
                        return Accounts::findFirstById($account_model->getAccountId()) ? true : false;
                    }
                ]
            )
        );
        return $this->validate($validator);
    }

    public function initialize()
    {
        $this->belongsTo('account_id', 'App\Models\Accounts', 'id', ['alias' => 'accounts']);
    }

    public static function findByAccount($accountId, $order = null, $columns = null)
    {
        if ($columns == null)
            return parent::find(['account_id = :accountId:',
                'bind' => ['accountId' => $accountId],
                'order' => $order]);
        else {
            return parent::find(['columns' => $columns, 'account_id = :accountId:',
                'bind' => ['accountId' => $accountId],
                'order' => $order]);
        }
    }

    /*public static function findByCompany(int $companyId, string $model, array $columns)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');
        $result = $modelsManager->createBuilder()
            ->columns($columns)
            ->from(["n" => $model])
            ->join('App\Models\Accounts', 'n.account_id = a.id', 'a')
            ->where('a.company_id = :companyId: and n.deleted = false', ['companyId' => $companyId])
            ->getQuery()
            ->execute();

        return $result->toArray();
    }*/

    private static function findByTemplate(array $columns, string $model, string $result_condition, array $result_bind)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');
        if ($columns != null) {
            $result = $modelsManager->createBuilder()
                ->columns($columns)
                ->from(["m" => $model])
                ->join('App\Models\Accounts', 'm.account_id = a.id', 'a')
                ->where($result_condition, $result_bind)
                ->getQuery()
                ->execute();
        } else {
            $result = $modelsManager->createBuilder()
                ->from(["m" => $model])
                ->join('App\Models\Accounts', 'm.account_id = a.id', 'a')
                ->where($result_condition, $result_bind)
                ->getQuery()
                ->execute();
        }

        return $result;
    }

    public static function findByUser($userId, string $model, array $columns = null,
                                      array $conditions = null, array $binds = null)
    {
        $result_condition = "a.company_id is null and a.user_id = :userId: and m.deleted = false";
        if ($conditions != null) {
            foreach ($conditions as $condition) {
                $result_condition .= ' and ' . $condition;
            }
        }

        $result_bind = ['userId' => $userId];

        if ($binds != null) {
            foreach ($binds as $key => $bind) {
                $result_bind[$key] = $bind;
            }
        }

        return self::findByTemplate($columns,$model,$result_condition,$result_bind);
    }

    public static function findByCompany($companyId, string $model, array $columns = null,
                                         array $conditions = null, array $binds = null)
    {
        $result_condition = "a.company_id = :companyId: and m.deleted = false";
        if ($conditions != null) {
            foreach ($conditions as $condition) {
                $result_condition .= ' and ' . $condition;
            }
        }

        $result_bind = ['companyId' => $companyId];

        if ($binds != null) {
            foreach ($binds as $key => $bind) {
                $result_bind[$key] = $bind;
            }
        }

        return self::findByTemplate($columns,$model,$result_condition,$result_bind);
    }
}