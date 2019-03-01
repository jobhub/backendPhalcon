<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class InvitesRegisterToBeManager extends InvitesFromCompaniesModel
{

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $invited;

    /**
     * Method to set the value of field invited
     *
     * @param string $invited
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
     * @return string
     */
    public function getInvited()
    {
        return $this->invited;
    }

    public function validation()
    {
        $validator = new Validation();

        return parent::validation()&&$this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("invites_register_to_be_manager");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'invites_register_to_be_manager';
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
