<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class InvitesCompanyManager extends InvitesFromCompaniesModel
{

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $invited;


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
     * Returns the value of field invited
     *
     * @return integer
     */
    public function getInvited()
    {
        return $this->invited;
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

        return parent::validation() && $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("invites_company_manager");
        $this->belongsTo('invited', 'App\Models\Users', 'user_id', ['alias' => 'InvitedPerson']);
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
