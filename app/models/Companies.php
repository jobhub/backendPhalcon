<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class Companies extends NotDeletedModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $companyId;

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
    protected $fullName;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=true)
     */
    protected $TIN;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $regionId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $userId;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    protected $webSite;

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
    protected $isMaster;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deleted;

    /**
     * Method to set the value of field companyId
     *
     * @param integer $companyId
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

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
     * @param string $fullName
     * @return $this
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Method to set the value of field tIN
     *
     * @param string $tIN
     * @return $this
     */
    public function setTIN($tIN)
    {
        $this->TIN = $tIN;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * Method to set the value of field userId
     *
     * @param integer $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Method to set the value of field webSite
     *
     * @param string $webSite
     * @return $this
     */
    public function setWebSite($webSite)
    {
        $this->webSite = $webSite;

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
     * @param string $isMaster
     * @return $this
     */
    public function setIsMaster($isMaster)
    {
        $this->isMaster = $isMaster;

        return $this;
    }

    /**
     * Method to set the value of field deleted
     *
     * @param string $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Returns the value of field companyId
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->companyId;
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
        return $this->fullName;
    }

    /**
     * Returns the value of field tIN
     *
     * @return string
     */
    public function getTIN()
    {
        return $this->TIN;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Returns the value of field webSite
     *
     * @return string
     */
    public function getWebSite()
    {
        return $this->webSite;
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
     * Returns the value of field isMaster
     *
     * @return string
     */
    public function getIsMaster()
    {
        return $this->isMaster;
    }

    /**
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
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

        if ($this->getWebSite() != null)
            $validator->add(
                'webSite',
                new UrlValidator(
                    [
                        'model' => $this,
                        'message' => 'Введите, пожалуйста, корректный URL',
                    ]
                )
            );

        if ($this->getTIN() != null)
            $validator->add(
                'TIN',
                new Regex(
                    [
                        "pattern" => "/^(\d{10}|\d{12})$/",
                        "message" => "Введите корректный ИНН",
                    ]
                )
            );

        if ($this->getRegionId() != null) {
            $validator->add(
                'regionId',
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

        $validator->add(
            'userId',
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
        );


        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("companies");
        $this->hasMany('companyId', '\CompaniesCategories', 'companyId', ['alias' => 'CompaniesCategories']);
        $this->hasMany('companyId', '\PhonesCompanies', 'companyId', ['alias' => 'PhonesCompanies']);
        $this->belongsTo('userId', '\Users', 'userId', ['alias' => 'Users']);
        $this->belongsTo('regionId', '\Regions', 'regionId', ['alias' => 'Regions']);
    }

    public function delete($delete = false, $data = null, $whiteList = null)
    {
        try {
            // Создаем менеджера транзакций
            $manager = new TxManager();
            // Запрос транзакции
            $transaction = $manager->get();
            $this->setTransaction($transaction);
            $result = parent::delete($delete, $data, $whiteList);

            if (!$result) {
                $transaction->rollback(
                    "Невозможно удалить компанию"
                );
                return $result;
            }

            if (!$delete) {
                //каскадное 'удаление' точек оказания услуг
                $tradePoints = TradePoints::findByCompanyId($this->getCompanyId());
                foreach ($tradePoints as $tradePoint)
                    $tradePoint->setTransaction($transaction);
                if (!$tradePoints->delete()) {
                    $transaction->rollback(
                        "Невозможно удалить точки оказания услуг"
                    );
                    return false;
                }

                //каскадное 'удаление' новостей
                $news = News::find(["subjectId = :companyId: ANd newType = 1",
                    'bind' =>
                        ['companyId' => $this->getCompanyId()
                        ]]);
                foreach ($news as $new)
                    $new->setTransaction($transaction);
                if (!$news->delete()) {
                    $transaction->rollback(
                        "Невозможно удалить новости компании"
                    );
                    return false;
                }
                $transaction->commit();
                return true;
            } else {
                $transaction->commit();
                return $result;
            }
        } catch (TxFailed $e) {
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

    public static function checkUserHavePermission($userId, $companyId, $right = null)
    {
        $managerRights = ['edit', 'addService', 'editService'];

        $company = Companies::findFirstByCompanyId($companyId);
        $user = Users::findFirstByUserId($userId);

        if (!$company)
            return false;

        //владелец и модераторы могут все
        if ($company->getUserId() == $userId || $user->getRole() == ROLE_MODERATOR) {
            return true;
        } else {
            $companiesManagers = CompaniesManagers::findFirst(
                ['companyId = :companyId: AND userId = :userId:',
                    'bind' => ['companyId' => $companyId, 'userId' => $userId]]);

            if (!$companiesManagers)
                return false;

            if($right == null)
                return false;

            foreach ($managerRights as $managerRight) {
                if ($managerRight == $right)
                    return true;
            }
        }
        return false;
    }
}
