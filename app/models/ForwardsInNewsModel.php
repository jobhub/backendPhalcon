<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

use Phalcon\DI\FactoryDefault as DI;

class ForwardsInNewsModel extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $account_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $object_id;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=true)
     */
    protected $forward_text;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $forward_date;

    /**
     * Method to set the value of field account_id
     *
     * @param integer $account_id
     * @return $this
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;

        return $this;
    }

    /**
     * Method to set the value of field object_id
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
     * Method to set the value of field forward_text
     *
     * @param string $forward_text
     * @return $this
     */
    public function setForwardText($forward_text)
    {
        $this->forward_text = $forward_text;

        return $this;
    }

    /**
     * Method to set the value of field forward_date
     *
     * @param string $forward_date
     * @return $this
     */
    public function setForwardDate($forward_date)
    {
        $this->forward_date = $forward_date;

        return $this;
    }

    /**
     * Returns the value of field account_id
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Returns the value of field object_id
     *
     * @return integer
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Returns the value of field forward_text
     *
     * @return string
     */
    public function getForwardText()
    {
        return $this->forward_text;
    }

    /**
     * Returns the value of field forward_date
     *
     * @return string
     */
    public function getForwardDate()
    {
        return $this->forward_date;
    }

    public function validation()
    {
        $validator = new Validation();

        if($this->getForwardDate()!=null)
        $validator->add(
            'forward_date',
            new Callback(
                [
                    "message" => "Время репоста должно быть раньше текущего",
                    "callback" => function ($forward) {
                        if (strtotime($forward->getForwardDate()) <= time())
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
        $this->setSource("forwards_in_news_model");
        $this->belongsTo('account_id', 'App\Models\Accounts', 'id', ['alias' => 'Accounts']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'forwards_in_news_model';
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

    public static function handleObjectWithForwards($model, array $handledObject, $objectId, $accountId = null)
    {
        $handledObject['stats']['forwards'] = self::getCount($model,$objectId);
        return $handledObject;
    }

    public static function getCount($model, $objectId){
        $db = DI::getDefault()->getDb();

        $sql = 'Select COUNT(*) from '.$model::getSource().' where object_id = :objectId';

        $query = $db->prepare($sql);
        $query->execute([
            'objectId' => $objectId
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC)[0]['count'];
    }

    public static function findByIds($model, $accountId, $objectId){
        $account = Accounts::findFirstById($accountId);

        return $model::findFirst(['account_id = ANY (:accounts:) and object_id = :objectId:','bind'=>
        [
            'accounts'=>$account->getRelatedAccounts(),
            'objectId'=>$objectId
        ]]);
    }
}
