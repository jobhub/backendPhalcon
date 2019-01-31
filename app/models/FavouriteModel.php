<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavouriteModel extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $object_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subject_id;

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
     * Method to set the value of field companyId
     *
     * @param integer $object_id
     * @return $this
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;

        return $this;
    }

    /**
     * Method to set the value of field userId
     *
     * @param integer $subject_id
     * @return $this
     */
    public function setSubjectId($subject_id)
    {
        $this->subject_id = $subject_id;

        return $this;
    }

    /**
     * Returns the value of field companyId
     *
     * @return integer
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getSubjectId()
    {
        return $this->subject_id;
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
        $this->setSource("favourite_model");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favourite_model';
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

    public static function findByIds($model,$subjectId,$objectId)
    {
        $account = Accounts::findFirstById($subjectId);

        return $model::find(['subject_id = ANY :accounts and object_id = :objectId','bind'=>
            [
                'accounts'=>$account->getRelatedAccounts(),
                'objectId'=>$objectId
            ]]);
    }
}
