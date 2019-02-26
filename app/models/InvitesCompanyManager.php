<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class InvitesCompanyManager extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $invite_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $invited;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $who_invited;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $where_invited;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $invite_date;

    /**
     * Method to set the value of field invite_id
     *
     * @param integer $invite_id
     * @return $this
     */
    public function setInviteId($invite_id)
    {
        $this->invite_id = $invite_id;

        return $this;
    }

    /**
     * Method to set the value of field invited
     *
     * @param integer $invited
     * @return $this
     */
    public function setInvited($invited)
    {
        $this->invited = $invited;

        return $this;
    }

    /**
     * Method to set the value of field who_invited
     *
     * @param integer $who_invited
     * @return $this
     */
    public function setWhoInvited($who_invited)
    {
        $this->who_invited = $who_invited;

        return $this;
    }

    /**
     * Method to set the value of field where_invited
     *
     * @param integer $where_invited
     * @return $this
     */
    public function setWhereInvited($where_invited)
    {
        $this->where_invited = $where_invited;

        return $this;
    }

    /**
     * Method to set the value of field invite_date
     *
     * @param string $invite_date
     * @return $this
     */
    public function setInviteDate($invite_date)
    {
        $this->invite_date = $invite_date;

        return $this;
    }

    /**
     * Returns the value of field invite_id
     *
     * @return integer
     */
    public function getInviteId()
    {
        return $this->invite_id;
    }

    /**
     * Returns the value of field invited
     *
     * @return integer
     */
    public function getInvited()
    {
        return $this->invited;
    }

    /**
     * Returns the value of field who_invited
     *
     * @return integer
     */
    public function getWhoInvited()
    {
        return $this->who_invited;
    }

    /**
     * Returns the value of field where_invited
     *
     * @return integer
     */
    public function getWhereInvited()
    {
        return $this->where_invited;
    }

    /**
     * Returns the value of field invite_date
     *
     * @return string
     */
    public function getInviteDate()
    {
        return $this->invite_date;
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'invited',
            new Callback(
                [
                    "message" => "Приглашенный пользователь не существует",
                    "callback" => function ($invite) {
                        return $invite->InvitedPerson?true:false;
                    }
                ]
            )
        );
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

        $validator->add(
            'invited',
            new Callback(
                [
                    "message" => "Пользователь уже связан с компанией",
                    "callback" => function ($invite) {

                        $related = Accounts::checkUserRelatesWithCompany($invite->getInvited(),
                            $invite->getWhereInvited());

                        return !$related;
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
        $this->setSource("invites_company_manager");
        $this->belongsTo('invited', 'App\Models\Users', 'user_id', ['alias' => 'InvitedPerson']);
        $this->belongsTo('where_invited', 'App\Models\Companies', 'company_id', ['alias' => 'WhereInvited']);
        $this->belongsTo('who_invited', 'App\Models\Accounts', 'id', ['alias' => 'WhoInvited']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'invites_company_manager';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return InvitesCompanyManager[]|InvitesCompanyManager|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return InvitesCompanyManager|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
