<?php
namespace App\Models;

use App\Libs\SupportClass;
use http\Client\Curl\User;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

use Phalcon\DI\FactoryDefault as DI;
use App\Libs\ImageLoader;

class Users extends NotDeletedModelWithCascade
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    protected $email;

    /**
     *
     * @var integer
     * @Column(type="integer", nullable=false)
     */
    protected $phone_id;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    protected $password;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $role;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $is_social;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $activated;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date_registration;


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
     * Method to set the value of field phoneId
     *
     * @param integer $phone_id
     * @return $this
     */
    public function setPhoneId($phone_id)
    {
        $this->phone_id = $phone_id;

        return $this;
    }

    /**
     * Method to set the value of field password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $security = DI::getDefault()->getSecurity();
        $this->password = $security->hash($password);

        return $this;
    }

    /**
     * Method to set the value of field role
     *
     * @param string $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
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
     * Returns the value of field email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the value of field phoneId
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phone_id;
    }

    /**
     * Returns the value of field password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the value of field role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    public function getDateRegistration()
    {
        return $this->date_registration;
    }

    public function setIsSocial($is_social)
    {
        $this->is_social = $is_social;

        return $this;
    }

    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    public function getIsSocial()
    {
        return $this->is_social;
    }

    public function getActivated()
    {
        return $this->activated;
    }

    public static function findByLogin($login)
    {
        $phone = $login;
        $email = $login;
        $formatPhone = Phones::formatPhone($phone);
        $phoneObj = Phones::findFirstByPhone($formatPhone);
        $user = Users::findFirst(
            [
                "(email = :email: OR phone_id = :phoneId:)",
                "bind" => [
                    "email" => $email,
                    "phoneId" => $phoneObj ? $phoneObj->getPhoneId() : null
                ]
            ]
        );
        return $user;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if (!$this->getIsSocial() && $this->getPhoneId() == null)
            $validator->add(
                'email',
                new EmailValidator(
                    [
                        'model' => $this,
                        'message' => 'Введите, пожалуйста, правильный адрес',
                    ]
                )
            );

        if (!$this->getIsSocial() && $this->getEmail() == null)
            $validator->add(
                'phone_id',
                new Callback(
                    [
                        "message" => "Необходимо указать либо номер телефона, либо email",
                        "callback" => function ($user) {
                            $phone = Phones::findFirstByPhoneId($user->getPhoneId());
                            if ($phone)
                                return true;
                            return false;
                        }
                    ]
                )
            );

        $validator->add(
            'role',
            new PresenceOf(
                [
                    "message" => "Не указана роль пользователя",
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
        //$this->setSchema("service_services");
        $this->setSource("users");
        $this->hasOne('user_id', 'App\Models\Userinfo', 'user_id', ['alias' => 'Userinfo']);
        $this->belongsTo('phone_id', 'Phones', 'phone_id', ['alias' => 'Phones']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'users';
    }

    public function getSequenceName() {
        return "users_userid_seq";
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
     * Восстанавливает отмеченного как удаленного пользователя
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

    public function getFinishedTasks()
    {
        // $query = $this->modelsManager->createQuery('SELECT COUNT(*) AS c FROM offers, auctions, tasks, users WHERE offers.userId=users.userId AND users.userId=:userId: AND auctions.selectedOffer=offers.offerId AND tasks.taskId=auctions.taskId AND tasks.status=\'Завершено\'');
        $query = $this->modelsManager->createQuery(
            'SELECT COUNT(*) AS c FROM offers INNER JOIN auctions ON offers.auctionId = auctions.auctionId
              INNER JOIN tasks ON auctions.taskId = auctions.taskId
              WHERE offers.userId = :userId: AND offers.selected = 1 AND tasks.status=\'Завершено\'');

        $count = $query->execute(
            [
                'userId' => "$this->userid",
            ]
        );
        $count = $count[0]['c'];
        return $count;
    }

    public function getRatingForCategory($idCategory)
    {
        $query = $this->getModelsManager()->createQuery('SELECT AVG(reviews.raiting) AS a FROM reviews, auctions, tasks, users WHERE tasks.categoryId=:categoryId: AND tasks.taskId=auctions.taskId AND auctions.auctionId=reviews.auctionId AND reviews.userId_object=:userId: AND reviews.executor=1');
        $avg = $query->execute(
            [
                'userId' => "$this->userid",
                'categoryId' => $idCategory
            ]
        );
        $avg = $avg[0]['a'];
        return $avg;
    }

    /**
     * @param $user_id
     * @return array
     */
    public function getUserShortInfo($user_id = null){
        $userInfo = $this->getRelated('Userinfo', [
            'columns' => Userinfo::shortColumns
        ]);
        if(! $userInfo)
            return [
            'user_id' => $this->user_id ,
                'email' => $this->email
            ];

            return $userInfo->toArray();
    }

    /**
     * @return string - array of accounts in postgresql format
     */
    public function getRelatedAccounts()
    {
        $accounts_obj = Accounts::findFirst(['user_id = :userId: and company_id is null',
            'bind'=>[
                'userId'=>$this->getUserId()
            ]]);

        $accounts[] = $accounts_obj->getId();
        return SupportClass::to_pg_array($accounts);
    }

    /**
     * @param $id
     * @return bool
     */
    public static function isUserExist($id){
        if(is_null($id) || empty($id))
            return false;
        $user = parent::findFirst($id);
        if(!$user)
            return false;
        return true;
    }
}
