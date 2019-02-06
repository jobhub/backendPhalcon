<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

use App\Libs\ImageLoader;
use App\Libs\SupportClass;

class Companies extends NotDeletedModelWithCascade
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $company_id;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=false)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=false)
     */
    protected $full_name;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=true)
     */
    protected $tin;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $region_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    protected $website;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    protected $email;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $is_master;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    protected $logotype;

    protected $rating_executor;
    protected $rating_client;
    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    const publicColumns = ['company_id', 'name', 'full_name', 'tin',
        'region_id', /*'user_id',*/
        'website', 'email', 'logotype', 'rating_executor', 'rating_client'];

    const publicColumnsInStr = 'company_id, name, full_name, tin,
        region_id, website, email, logotype, rating_executor, rating_client';

    const shortColumns = ['company_id', 'name', 'logotype'];

    const shortColumnsInStr = 'company_id, name, logotype';

    /**
     * Method to set the value of field companyId
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
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field fullName
     *
     * @param string $full_name
     * @return $this
     */
    public function setFullName($full_name)
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * Method to set the value of field tIN
     *
     * @param string $TIN
     * @return $this
     */
    public function setTIN($TIN)
    {
        $this->tin = $TIN;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $region_id
     * @return $this
     */
    public function setRegionId($region_id)
    {
        $this->region_id = $region_id;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field userId
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
     * Method to set the value of field webSite
     *
     * @param string $website
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Method to set the value of field email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Method to set the value of field isMaster
     *
     * @param string $is_master
     * @return $this
     */
    public function setIsMaster($is_master)
    {
        $this->is_master = $is_master;

        return $this;
    }

    /**
     * Method to set the value of field logotype
     *
     * @param string $logotype
     * @return $this
     */
    public function setLogotype($logotype)
    {
        $this->logotype = $logotype;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;
    }


    public function setRatingExecutor($rating_executor)
    {
        $this->rating_executor = $rating_executor;

        return $this;
    }

    public function setRatingClient($rating_client)
    {
        $this->rating_client = $rating_client;

        return $this;
    }

    /**
     * Returns the value of field companyId
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->company_id;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Returns the value of field tIN
     *
     * @return string
     */
    public function getTIN()
    {
        return $this->tin;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->region_id;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field webSite
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Returns the value of field email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the value of field ismaster
     *
     * @return string
     */
    public function getIsMaster()
    {
        return $this->is_master;
    }

    /**
     * Returns the value of field logotype
     *
     * @return string
     */
    public function getLogotype()
    {
        return $this->logotype;
    }

    public function getRatingExecutor()
    {
        return $this->rating_executor;
    }

    public function getRatingClient()
    {
        return $this->rating_client;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if ($this->getEmail() != null)
            $validator->add(
                'email',
                new EmailValidator(
                    [
                        'model' => $this,
                        'message' => 'Введите, пожалуйста, корректный email',
                    ]
                )
            );

        if ($this->getWebsite() != null)
            $validator->add(
                'website',
                new UrlValidator(
                    [
                        'model' => $this,
                        'message' => 'Введите, пожалуйста, корректный URL',
                    ]
                )
            );

        if ($this->getTIN() != null)
            $validator->add(
                'tin',
                new Regex(
                    [
                        "pattern" => "/^(\d{10}|\d{12})$/",
                        "message" => "Введите корректный ИНН",
                    ]
                )
            );

        if ($this->getRegionId() != null) {
            $validator->add(
                'region_id',
                new Callback(
                    [
                        "message" => "Такой регион не существует",
                        "callback" => function ($company) {
                            $region = Regions::findFirstByRegionId($company->getRegionId());

                            if ($region)
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        /*$validator->add(
            'user_id',
            new Callback(
                [
                    "message" => "Такого пользователя не существует",
                    "callback" => function ($company) {
                        $user = Users::findFirstByUserId($company->getUserId());

                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );*/

        if ($this->getLogotype() != null)
            $validator->add(
                'logotype',
                new Callback(
                    [
                        "message" => "Формат логотипа не поддерживается",
                        "callback" => function ($company) {
                            $format = pathinfo($company->getLogotype(), PATHINFO_EXTENSION);

                            if ($format == 'jpeg' || 'jpg')
                                return true;
                            elseif ($format == 'png')
                                return true;
                            elseif ($format == 'gif')
                                return true;
                            else {
                                return false;
                            }
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
        $this->setSource("companies");
        $this->hasMany('company_id', 'App\Models\CompaniesCategories', 'company_id', ['alias' => 'CompaniesCategories']);
        $this->hasMany('company_id', 'App\Models\PhonesCompanies', 'company_id', ['alias' => 'PhonesCompanies']);
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
        $this->belongsTo('region_id', 'App\Models\Regions', 'region_id', ['alias' => 'Regions']);
    }

    public function delete($delete = false, $deletedCascade = false, $data = null, $whiteList = null)
    {
        if (!$delete) {
            try {
                // Создаем менеджера транзакций
                $manager = new TxManager();
                // Запрос транзакции
                $transaction = $manager->get();
                $this->setTransaction($transaction);

                Accounts::cascadeDeletingByAccountIds($this->getRelatedAccounts(),$transaction);

                $result = parent::delete($delete, false, $data, $whiteList);

                if (!$result) {
                    $transaction->rollback(
                        "Невозможно удалить компанию"
                    );
                    return $result;
                }

                $transaction->commit();
                return true;
            } catch (TxFailed $e) {
                $message = new Message(
                    $e->getMessage()
                );

                $this->appendMessage($message);
                return false;
            }
        } else {
            $logo = $this->getLogotype();

            $result = parent::delete($delete, false, $data, $whiteList);

            if ($result) {
                ImageLoader::delete($logo);
            }

            return $result;
        }
    }

    /**
     * Восстанавливает отмеченную как удаленную компанию
     * @return bool
     */
    public function restore()
    {
        try {
            $manager = new TxManager();
            // Запрос транзакции
            $transaction = $manager->get();
            $this->setTransaction($transaction);
            if (!parent::restore()) {
                $transaction->rollback(
                    "Невозможно восстановить компанию"
                );
                return false;
            }

            //Каскадное восстановление точек оказания услуг
            Accounts::cascadeRestoringByAccountIds($this->getRelatedAccounts(), $transaction);

            $transaction->commit();
            return true;
        } catch (TxFailed $e) {
            $message = new Message(
                $e->getMessage()
            );

            $this->appendMessage($message);
            return false;
        }
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'companies';
    }

    public function getSequenceName()
    {
        return "companies_companyid_seq";
    }

    public function beforeSave()
    {
        if ($this->getRatingClient() == null)
            $this->setRatingClient(5);
        if ($this->getRatingExecutor() == null)
            $this->setRatingExecutor(5);
    }

    public static function findCompanyById(int $companyId, array $columns = null)
    {
        if ($columns == null)
            return self::findFirst(['company_id = :companyId:',
                'bind' => ['companyId' => $companyId]]);
        else {
            return self::findFirst(['columns' => $columns, 'company_id = :companyId:',
                'bind' => ['companyId' => $companyId]]);
        }
    }

    public static function findCompaniesByUserOwner(int $userId)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');

        $columns = [];
        foreach (self::publicColumns as $publicColumn) {
            $columns[] = 'c.' . $publicColumn;
        }
        $result = $modelsManager->createBuilder()
            ->columns($columns)
            ->from(["c" => "App\Models\Companies"])
            ->join('App\Models\Accounts', 'c.company_id = a.company_id and a.company_role_id = :role:', 'a')
            ->where('a.user_id = :userId: and c.deleted = false',
                [
                    'userId' => $userId,
                    'role' => CompanyRole::ROLE_OWNER_ID
                ])
            ->getQuery()
            ->execute();

        return self::handleCompanyFromArray($result->toArray());
    }

    public static function handleCompanyFromArray(array $companies)
    {
        $result = [];
        foreach ($companies as $company) {
            $company['phones'] = PhonesCompanies::getCompanyPhones($company['company_id']);

            $result[] = $company;
        }

        return $result;
    }

    /**
     * @return string - array of accounts in postgresql format
     */
    public function getRelatedAccounts()
    {
        $accounts_obj = Accounts::findByCompanyId($this->getCompanyId());
        $accounts = [];
        foreach ($accounts_obj as $account) {
            $accounts[] = $account->getId();
        }
        return SupportClass::to_pg_array($accounts);
    }
}
