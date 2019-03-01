<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use App\Libs\SupportClass;
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

    const DEFAULT_RESULT_PER_PAGE = 10;

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
                    "message" => "Такой аккаунт не существует или уже подписан",
                    "callback" => function ($fav_model) {
                        $account_exists = Accounts::findFirstById($fav_model->getSubjectId());
                        if ($account_exists) {
                            if ($account_exists->getCompanyId() != null) {
                                /*$accounts = Accounts::findByCompanyId($account_exists->getCompanyId());

                                $ids = [];
                                foreach ($accounts as $account) {
                                    $ids[] = $account->getId();
                                }

                                $ids = SupportClass::to_pg_array($ids);*/

                                $exists = self::findFirst(['subject_id = ANY(:ids:) and object_id = :objectId:', 'bind' =>
                                    ['ids' => $account_exists->getRelatedAccounts(), 'objectId'=>$fav_model->getObjectId()]]);

                                return $exists ? false : true;
                            }
                            return true;
                        }
                        return false;
                    }])
        );

        if ($this->getFavouriteDate() != null)
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

    public static function findByIds($model, $subjectId, $objectId)
    {
        $account = Accounts::findFirstById($subjectId);

        return $model::findFirst(['subject_id = ANY (:accounts:) and object_id = :objectId:', 'bind' =>
            [
                'accounts' => $account->getRelatedAccounts(),
                'objectId' => $objectId
            ]]);
    }

    public static function findFavourites($subjectId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $account = Accounts::findFirstById($subjectId);

        $favs = self::find(['subject_id = ANY (:subjectId:)', 'bind' => [
            'subjectId' => $account->getRelatedAccounts()
        ], 'offset' => $offset, 'limit' => $page_size, 'order'=>'favourite_date desc']);

        return self::handleFavourites($favs->toArray());
    }

    public static function handleFavourites($favs)
    {
        $handledFavs = [];
        foreach ($favs as $fav) {
            $handledFavs[] = self::handleFavourite($fav);
        }
        return $handledFavs;
    }

    public static function handleFavourite($fav)
    {
        return $fav;
    }

    public static function handleSubscribers($favs)
    {

        $session = DI::getDefault()->get('session');
        $accountId = $session->get('accountId');

        $account = Accounts::findFirstById($accountId);

        if (!$account)
            return null;

        $handledFavs = [];
        foreach ($favs as $fav) {

            $handledFav = self::handleSubscriber($fav, $account->getRelatedAccounts());
            if ($handledFav != null)
                $handledFavs[] = $handledFav;
        }
        return $handledFavs;
    }

    public static function handleSubscriber($fav, string $currentAccountIds)
    {
        $account = Accounts::findFirstById($fav['subject_id']);

        if (!$account)
            return null;

        if ($account->getCompanyId() != null) {
            $subscriber = Companies::findCompanyById($account->getCompanyId(),
                Companies::shortColumns);

            if (!$subscriber)
                return null;


            $subscribed = FavoriteCompanies::findFirst(['subject_id = ANY(:currentAccountId:) 
            and object_id = :companyId:', 'bind' => [
                'currentAccountId' => $currentAccountIds,
                'companyId' => $account->getCompanyId()
            ]]);
        } else {

            $subscriber = Userinfo::findUserInfoById($account->getUserId(),
                Userinfo::shortColumns);

            if (!$subscriber)
                return null;

            $subscribed = FavoriteUsers::findFirst(['subject_id = ANY(:currentAccountId:) 
            and object_id = :userId:', 'bind' => [
                'currentAccountId' => $currentAccountIds,
                'userId' => $account->getUserId()
            ]]);

            $handledFavUser['subscribed'] = $subscribed ? true : false;
        }
        $resp = $subscriber->toArray();
        $resp['subscribed'] = $subscribed ? true : false;
        return $resp;
    }

    public static function handleSubscriptions($favs)
    {
        $session = DI::getDefault()->get('session');
        $accountId = $session->get('accountId');

        $account = Accounts::findFirstById($accountId);

        if (!$account)
            return null;

        $handledFavs = [];
        foreach ($favs as $fav) {

            $handledFav = self::handleSubscription($fav);
            if ($handledFav != null)
                $handledFavs[] = $handledFav;
        }
        return $handledFavs;
    }

    public static function handleSubscription($fav)
    {

        if ($fav['relation'] == 'favorite_companies') {
            $subscription = Companies::findCompanyById($fav['object_id'],
                Companies::shortColumns);

            if (!$subscription)
                return null;

            $handledFav = [
                'subscription' => $subscription
            ];
        } else {
            $subscription = Userinfo::findUserInfoById($fav['object_id'],
                Userinfo::shortColumns);

            if (!$subscription)
                return null;

           /* $handledFav = [
                'subscription' => $subscription,
            ];*/
        }
        $resp = $subscription->toArray();
        $resp['subscribed'] = false;
        return $resp;
    }


    public static function findSubscribers(Accounts $account, $query, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        if ($account->getCompanyId() != null) {
            if (empty(trim($query))) {
                $result = FavoriteCompanies::find(['object_id = :companyId:', 'bind' => [
                    'companyId' => $account->getCompanyId()
                ], 'offset' => $offset, 'limit' => $page_size])->toArray();
            } else {
                $db = DI::getDefault()->getDb();

                $sql = 'Select * FROM (
    (select fav_comp.* from favorite_companies as fav_comp
    			inner join accounts a on (a.id = fav_comp.subject_id and a.company_id is null)
                inner join userinfo on (userinfo.user_id = a.user_id)
                inner join users on (users.user_id = userinfo.user_id)
                where fav_comp.object_id = :companyId and users.deleted = false
                and ( 
                    ((first_name || \' \'|| last_name || \' \'|| userinfo.email || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                     OR
                    ((first_name || \' \'|| last_name || \' \'|| patronymic || \' \'|| userinfo.email
                    || \' \'|| nickname) 
                     ilike \'%\'||:query||\'%\')
                    OR
                    ((first_name || \' \'|| last_name || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                    OR
                    ((first_name || \' \'|| last_name || \' \'|| patronymic || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                    ))
    UNION ALL
    (select fav_comp.* from favorite_companies as fav_comp
    			inner join accounts a on (a.id = fav_comp.subject_id and a.company_id is not null)
                inner join companies on (companies.company_id = a.company_id)
                where fav_comp.object_id = :companyId and companies.deleted = false
    			and (
                    ((name || \' \'|| full_name) ilike \'%\'||:query||\'%\')
                    or ((name) ilike \'%\'||:query||\'%\')
                    )      
    )
    ) as foo 
    order by foo.favourite_date desc
                LIMIT :limit
                OFFSET :offset';

                $query_sql = $db->prepare($sql);
                $query_sql->execute([
                    'companyId' => $account->getCompanyId(),
                    'query' => $query,
                    'limit' => $page_size,
                    'offset' => $offset,
                ]);

                $result = $query_sql->fetchAll(\PDO::FETCH_ASSOC);
            }
        } else {
            if (empty(trim($query))) {
                $result = FavoriteUsers::find(['object_id = :userId:', 'bind' => [
                    'userId' => $account->getUserId()
                ], 'offset' => $offset, 'limit' => $page_size])->toArray();
            } else {
                $db = DI::getDefault()->getDb();

                $sql = 'Select * FROM (
    (select fav_user.* from favorite_users as fav_user
    			inner join accounts a on (a.id = fav_user.subject_id and a.company_id is null)
                inner join userinfo on (userinfo.user_id = a.user_id)
                inner join users on (users.user_id = userinfo.user_id)
                where fav_user.object_id = :userId and users.deleted = false
                and ( 
                    ((first_name || \' \'|| last_name || \' \'|| userinfo.email || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                     OR
                    ((first_name || \' \'|| last_name || \' \'|| patronymic || \' \'|| userinfo.email
                    || \' \'|| nickname) 
                     ilike \'%\'||:query||\'%\')
                    OR
                    ((first_name || \' \'|| last_name || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                    OR
                    ((first_name || \' \'|| last_name || \' \'|| patronymic || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                    ))
    UNION ALL
    (select fav_user.* from favorite_users as fav_user
    			inner join accounts a on (a.id = fav_user.subject_id and a.company_id is not null)
                inner join companies on (companies.company_id = a.company_id)
                where fav_user.object_id = 7 and companies.deleted = false
    			and (
                    ((name || \' \'|| full_name) ilike \'%\'||:query||\'%\')
                    or ((name) ilike \'%\'||:query||\'%\')
                    )      
    )
    ) as foo 
    order by foo.favourite_date desc
                LIMIT :limit
                OFFSET :offset';

                $query_sql = $db->prepare($sql);
                $query_sql->execute([
                    'userId' => $account->getUserId(),
                    'query' => $query,
                    'limit' => $page_size,
                    'offset' => $offset,
                ]);

                $result = $query_sql->fetchAll(\PDO::FETCH_ASSOC);
            }
        }

        return self::handleSubscribers($result);
    }

    public static function findSubscriptions(Accounts $account, $query, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        $db = DI::getDefault()->getDb();

        if (empty(trim($query))) {
            $sql = 'Select * FROM (
    (
    select fav_user.*, \'favorite_users\' as relation from favorite_users as fav_user
                inner join userinfo on (userinfo.user_id = fav_user.object_id)
                inner join users on (users.user_id = userinfo.user_id)
                where fav_user.subject_id = ANY(:ids) and users.deleted = false
    )
    UNION ALL
    (select fav_comp.*, \'favorite_companies\' as relation from favorite_companies as fav_comp
                inner join companies c on (c.company_id = fav_comp.object_id)
                where fav_comp.subject_id = ANY(:ids) and c.deleted = false
    )
    ) as foo 
    order by foo.favourite_date desc
                LIMIT :limit
                OFFSET :offset';

            $query_sql = $db->prepare($sql);
            $query_sql->execute([
                'ids' => $account->getRelatedAccounts(),
                'limit' => $page_size,
                'offset' => $offset,
            ]);

            $result = $query_sql->fetchAll(\PDO::FETCH_ASSOC);
        } else {

            $sql = 'Select * FROM (
    (
    select fav_user.*, \'favorite_users\' as relation from favorite_users as fav_user
                inner join userinfo on (userinfo.user_id = fav_user.object_id)
                inner join users on (users.user_id = userinfo.user_id)
                where fav_user.subject_id = ANY(:ids) and users.deleted = false
                and ( 
                    ((first_name || \' \'|| last_name || \' \'|| userinfo.email || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                     OR
                    ((first_name || \' \'|| last_name || \' \'|| patronymic || \' \'|| userinfo.email
                    || \' \'|| nickname) 
                     ilike \'%\'||:query||\'%\')
                    OR
                    ((first_name || \' \'|| last_name || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                    OR
                    ((first_name || \' \'|| last_name || \' \'|| patronymic || \' \'|| nickname) ilike \'%\'||:query||\'%\')
                    )
    )
    UNION ALL
    (select fav_comp.*, \'favorite_companies\' as relation from favorite_companies as fav_comp
                inner join companies c on (c.company_id = fav_comp.object_id)
                where fav_comp.subject_id = ANY(:ids) and c.deleted = false
    			   and (
                    ((name || full_name) ilike \'%\'||:query||\'%\')
                    or ((name) ilike \'%\'||:query||\'%\')
                    )   
    )
    ) as foo 
    order by foo.favourite_date desc
                LIMIT :limit
                OFFSET :offset';

            $query_sql = $db->prepare($sql);
            $query_sql->execute([
                'ids' => $account->getRelatedAccounts(),
                'query' => $query,
                'limit' => $page_size,
                'offset' => $offset,
            ]);

            $result = $query_sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        return self::handleSubscriptions($result);
    }

    public static function getSubscribersCount(Accounts $account)
    {
        $db = DI::getDefault()->getDb();
        if ($account->getCompanyId() != null) {

            $sql = 'Select COUNT(*) FROM favorite_companies fav_comp where fav_comp.object_id = :companyId';

            $query_sql = $db->prepare($sql);
            $query_sql->execute([
                'companyId' => $account->getCompanyId()
            ]);

            $result = $query_sql->fetchAll(\PDO::FETCH_ASSOC);

        } else {

            $sql = 'Select COUNT(*) FROM favorite_users fav_users where fav_users.object_id = :userId';

            $query_sql = $db->prepare($sql);
            $query_sql->execute([
                'userId' => $account->getUserId()
            ]);

            $result = $query_sql->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $result[0]['count'];
    }

    public static function getSubscriptionsCount(Accounts $account)
    {
        $db = DI::getDefault()->getDb();

        $sql = 'Select SUM(count) FROM (
    (
    select COUNT(*) count from favorite_users as fav_user
                inner join userinfo on (userinfo.user_id = fav_user.object_id)
                inner join users on (users.user_id = userinfo.user_id)
                where fav_user.subject_id = ANY(:ids) and users.deleted = false
    )
    UNION ALL
    (select COUNT(*) count from favorite_companies as fav_comp
                inner join companies c on (c.company_id = fav_comp.object_id)
                where fav_comp.subject_id = ANY(:ids) and c.deleted = false
    )
    ) as foo';

        $query_sql = $db->prepare($sql);
        $query_sql->execute([
            'ids' => $account->getRelatedAccounts()
        ]);

        $result = $query_sql->fetchAll(\PDO::FETCH_ASSOC);


        return $result[0]['sum'];
    }
}
