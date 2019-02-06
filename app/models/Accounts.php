<?php

namespace App\Models;

use App\Libs\SupportClass;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

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
        if ($account->getUserId() == $userId)
            return true;

        if ($account->getCompanyId() != null) {
            $account = Accounts::findFirst([
                'company_id = :companyId: and user_id = :userId: and company_role_id = :companyRoleId:',
                'bind' => [
                    'companyId' => $account->getCompanyId(),
                    'userId' => $userId,
                    'companyRoleId' => self::COMPANY_ROLE_OWNER
                ]
            ]);

            if ($account)
                return true;
        }

        return false;
    }

    public static function checkUserHavePermissionToCompany($userId, $companyId, $right = null)
    {
        $account = Accounts::findFirst(['user_id = :userId: and company_id = :companyId:',
            'bind' => [
                'userId' => $userId,
                'companyId' => $companyId
            ]]);

        if (!$account)
            return false;

        //check specific right
        return true;
    }

    public static function findAccountsByUser($userId)
    {
        return self::findByUserId($userId);
    }

    public function getUserInfomations()
    {
        if (!is_null($this->company_id)) {
            return Companies::findCompanyById($this->company_id, Companies::shortColumns);
        }
        return Userinfo::findUserInfoById($this->user_id, Userinfo::shortColumns);
    }

    /**
     * @return string - array of accounts in postgresql format
     */
    public function getRelatedAccounts()
    {
        if ($this->getCompanyId() == null) {
            $accounts = [$this->getId()];
        } else {
            $accounts_obj = Accounts::findByCompanyId($this->getCompanyId());
            $accounts = [];
            foreach ($accounts_obj as $account) {
                $accounts[] = $account->getId();
            }
        }
        return SupportClass::to_pg_array($accounts);
    }

    public static function checkAccountsRelated(Accounts $accountOne, Accounts $accountTwo)
    {
        if ($accountOne == null || $accountTwo == null)
            return false;

        if (($accountOne->getCompanyId() != null && $accountTwo->getCompanyId() != null
                && $accountOne->getCompanyId() == $accountTwo->getCompanyId())
            || ($accountOne->getCompanyId() == null && $accountTwo->getCompanyId() == null
                && $accountOne->getUserId() == $accountTwo->getUserId()))
            return true;

        return false;
    }

    public static function checkUserRelatesWithAccount($userId, $accountId)
    {
        $account = Accounts::findFirstById($accountId);

        if (!$account)
            return false;

        if ($account->getUserId() == $userId)
            return true;

        if ($account->getCompanyId() != null) {
            $account = Accounts::findFirst([
                'company_id = :companyId: and user_id = :userId:',
                'bind' => [
                    'companyId' => $account->getCompanyId(),
                    'userId' => $userId
                ]
            ]);

            if ($account)
                return true;
        }

        return false;
    }

    /**
     * Checks equal between two accounts as subjects.
     * If accounts created for one company then they are the equal.
     *
     *
     * @param int $accountId1
     * @param int $accountId2
     *
     * @return bool;
     */
    public static function equalsSubjects(int $accountId1, int $accountId2)
    {
        $account1 = Accounts::findFirstById($accountId1);

        if (!$account1)
            return false;

        $account2 = Accounts::findFirstById($accountId2);

        if (!$account2)
            return false;

        if ($account1->getCompanyId() != null && $account2->getCompanyId() != null
            && $account1->getCompanyId() == $account2->getCompanyId())
            return true;

        if ($account1->getCompanyId() == null && $account2->getCompanyId() == null
            && $account1->getUserId() == $account2->getUserId())
            return true;

        return false;
    }

    public static function addInformationForCabinet(Accounts $account, $data, Accounts $currentAccount = null)
    {
        $data['countNews'] = intval(News::getPublicationCount($account));

        $data['countSubscribers'] = FavouriteModel::getSubscribersCount($account);

        $data['countSubscriptions'] = intval(FavouriteModel::getSubscriptionsCount($account));

        if ($currentAccount != null && !self::checkAccountsRelated($account, $currentAccount)) {

            if ($account->getCompanyId() != null)
                $subscribed = FavoriteCompanies::findByIds('App\Models\FavoriteCompanies', $currentAccount->getId(), $account->getCompanyId());
            else
                $subscribed = FavoriteUsers::findByIds('App\Models\FavoriteUsers', $currentAccount->getId(), $account->getUserId());

            $data['subscribed'] = boolval($subscribed);
        }

        return $data;
    }

    /**
     * Каскадно помечает как удаленное все основные объекты. А именно:
     *      Точки оказания услуг
     *      Услуги
     *      Запросы на оказание услуг
     *      Заказы
     *      Предложения
     *      Новости
     *
     * @param string $accountIds - array of accounts in postgres format
     * @param $transaction - объект транзакции для отката изменений
     *
     * @exception Phalcon\Mvc\Model\Transaction\Failed - вызывается внутри метода, если не удалось отметить объект, как удаленный
     */
    public static function cascadeDeletingByAccountIds(string $accountIds, $transaction)
    {
        //каскадное 'удаление' точек оказания услуг
        TradePoints::cascadeDeletingByAccountIds($accountIds,$transaction);

        //каскадное 'удаление' новостей
        News::cascadeDeletingByAccountIds($accountIds,$transaction);

        Services::cascadeDeletingByAccountIds($accountIds,$transaction);

        Requests::cascadeDeletingByAccountIds($accountIds,$transaction);

        Tasks::cascadeDeletingByAccountIds($accountIds,$transaction);

        Offers::cascadeDeletingByAccountIds($accountIds,$transaction);
    }

    public static function cascadeRestoringByAccountIds(string $accountIds, $transaction)
    {
        //каскадное 'удаление' точек оказания услуг
        TradePoints::cascadeRestoringByAccountIds($accountIds,$transaction);

        //каскадное 'удаление' новостей
        News::cascadeRestoringByAccountIds($accountIds,$transaction);

        Services::cascadeRestoringByAccountIds($accountIds,$transaction);

        Requests::cascadeRestoringByAccountIds($accountIds,$transaction);

        Tasks::cascadeRestoringByAccountIds($accountIds,$transaction);

        Offers::cascadeRestoringByAccountIds($accountIds,$transaction);
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
