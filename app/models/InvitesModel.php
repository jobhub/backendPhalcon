<?php

namespace App\Models;

use App\Services\InviteService;

use Phalcon\DI\FactoryDefault as DI;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class InvitesModel extends \Phalcon\Mvc\Model
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

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("invites_model");
    }

    public function validation()
    {
        /*$validator = new Validation();

        return $this->validate($validator);*/
        return true;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'invites_model';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return InvitesModel[]|InvitesModel|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return InvitesModel|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findInviteByData($invited, $where_invited,
                                            $type = InviteService::TYPE_INVITE_TO_BE_MANAGER, $who_invited = null)
    {
        $inviteService = DI::getDefault()->get('inviteService');

        $model = $inviteService->getModelByType($type);

        if ($who_invited == null) {
            $invite = $model::findFirst([
                'invited = :invited: and where_invited = :where_invited:',
                'bind' => ['invited' => $invited, 'where_invited' => $where_invited]
            ]);
        } else {
            $invite = $model::findFirst([
                'invited = :invited: and where_invited = :where_invited: 
                    and who_invited = :who_invited:',
                'bind' => [
                    'invited' => $invited,
                    'where_invited' => $where_invited,
                    'who_invited' => $who_invited
                ]
            ]);
        }
        return $invite;
    }
}
