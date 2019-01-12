<?php

namespace App\Models;

class Accounts extends \Phalcon\Mvc\Model
{

    const COMPANY_ROLE_OWNER = 1;
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $company_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $company_role_id;

    /**
     * Method to set the value of field id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to set the value of field user_id
     *
     * @param integer $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Method to set the value of field company_id
     *
     * @param integer $company_id
     * @return $this
     */
    public function setCompanyId($company_id)
    {
        $this->company_id = $company_id;

        return $this;
    }

    /**
     * Method to set the value of field company_role_id
     *
     * @param integer $company_role_id
     * @return $this
     */
    public function setCompanyRoleId($company_role_id)
    {
        $this->company_role_id = $company_role_id;

        return $this;
    }

    /**
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field company_id
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->company_id;
    }

    /**
     * Returns the value of field company_role_id
     *
     * @return integer
     */
    public function getCompanyRoleId()
    {
        return $this->company_role_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("accounts");
        $this->belongsTo('company_id', 'App\Models\Companies', 'company_id', ['alias' => 'Companies']);
        $this->belongsTo('company_role_id', 'App\Models\CompanyRole', 'id', ['alias' => 'CompanyRole']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Accounts[]|Accounts|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Accounts|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }


    public static function findForUserDefaultAccount($userId)
    {
        return Accounts::findFirst([
            'user_id = :userId: and company_id is null',
            'bind' => ['userId' => $userId]
        ]);
    }

    /**
     * Realise simple logic for checking permission of user to specified account.
     * Or checking permission of user to act on behalf of specified account.
     * @param $userId
     * @param $accountId
     * @param null $right
     * @return bool
     */
    public static function checkUserHavePermission($userId, $accountId, $right = null)
    {
        $account = Accounts::findFirstById($accountId);

        if (!$account)
            return false;

        /*
         * It's simple logic without checking specified rights. If user is manager or owner, then he has rights.
         * Owner of company has rights to all accounts associated with this company.
         */
        if($account->getUserId() == $userId)
            return true;

        if($account->getCompanyId()!= null){
            $account = Accounts::findFirst([
                'company_id = :companyId: and user_id = :userId: and company_role_id = :companyRoleId:',
                'bind' => [
                    'companyId'=> $account->getCompanyId(),
                    'userId' => $userId,
                    'companyRoleId' => self::COMPANY_ROLE_OWNER
                ]
            ]);

            if($account)
                return true;
        }

        return false;
    }

    public static function checkUserHavePermissionToCompany($userId, $companyId, $right = null)
    {
        $account = Accounts::findFirst(['user_id = :userId: and company_id = :companyId:',
            'bind'=>[
                'userId'=>$userId,
                'companyId'=>$companyId
            ]]);

        if (!$account)
            return false;

        //check specific right
        return true;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'accounts';
    }

}
