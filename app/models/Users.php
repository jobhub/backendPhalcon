<?php
namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

use Phalcon\DI\FactoryDefault as DI;

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
        try {
            // Создаем менеджера транзакций
            $manager = new TxManager();
            // Запрос транзакции
            $transaction = $manager->get();
            $this->setTransaction($transaction);

            if (!$delete) {
                //каскадное 'удаление' точек оказания услуг
                $tradePoints = TradePoints::findBySubject($this->getUserId(), 0);
                foreach ($tradePoints as $tradePoint) {
                    $tradePoint->setTransaction($transaction);
                    if (!$tradePoint->delete(false, true)) {
                        $transaction->rollback(
                            "Невозможно удалить точки оказания услуг"
                        );
                        return false;
                    }
                }

                //каскадное 'удаление' новостей
                $news = News::find(["subjectid = :userId: AND subjecttype = 0",
                    'bind' =>
                        ['userId' => $this->getUserId()
                        ]]);
                foreach ($news as $new) {
                    $new->setTransaction($transaction);
                    if (!$new->delete(false, true)) {
                        $transaction->rollback(
                            "Невозможно удалить новости пользователя"
                        );
                        return false;
                    }
                }

                //каскадное 'удаление' услуг
                $services = Services::find(["subjectid = :userId: AND subjecttype = 0",
                    'bind' =>
                        ['userId' => $this->getUserId()
                        ]]);
                foreach ($services as $service) {
                    $service->setTransaction($transaction);
                    if (!$service->delete(false, true)) {
                        $transaction->rollback(
                            "Невозможно удалить услуги пользователя"
                        );
                        return false;
                    }
                }

                //каскадное 'удаление' запросов
                $requests = Requests::find(["subjectid = :userId: AND subjecttype = 0",
                    'bind' =>
                        ['userId' => $this->getUserId()
                        ]]);
                foreach ($requests as $request) {
                    $request->setTransaction($transaction);
                    if (!$request->delete(false, true)) {
                        $transaction->rollback(
                            "Невозможно удалить запросы пользователя"
                        );
                        return false;
                    }
                }

                //каскадное 'удаление' заданий
                $tasks = Tasks::find(["subjectid = :userId: AND subjecttype = 0",
                    'bind' =>
                        ['userId' => $this->getUserId()
                        ]]);
                foreach ($tasks as $task) {
                    $task->setTransaction($transaction);
                    if (!$task->delete(false, true)) {
                        $transaction->rollback(
                            "Невозможно удалить задания пользователя"
                        );
                        return false;
                    }
                }

                //каскадное 'удаление' предложений
                $offers = Offers::find(["subjectid = :userId: AND subjecttype = 0",
                    'bind' =>
                        ['userId' => $this->getUserId()
                        ]]);
                foreach ($offers as $offer) {
                    $offer->setTransaction($transaction);
                    if (!$offer->delete(false, true)) {
                        $transaction->rollback(
                            "Невозможно удалить предложения пользователя"
                        );
                        return false;
                    }
                }

                $result = parent::delete($delete, false, $data, $whiteList);

                if (!$result) {
                    $transaction->rollback(
                        "Невозможно удалить пользователя"
                    );
                    return $result;
                }

                $transaction->commit();
                return true;
            } else {
                $path = null;
                if ($this->userinfo != null) {
                    $path = $this->userinfo->getPathToPhoto();
                }

                $result = parent::delete($delete, false, $data, $whiteList);

                if ($result && $path != null) {
                    ImageLoader::delete($path);
                }

                $transaction->commit();
                return $result;
            }
        } catch (TxFailed $e) {
            return false;
        }
    }

    public function restore()
    {
        $manager = new TxManager();
        // Запрос транзакции
        $transaction = $manager->get();
        $this->setTransaction($transaction);
        if (!parent::restore()) {
            $transaction->rollback(
                "Невозможно восстановить пользователя"
            );
            return false;
        }

        //Каскадное восстановление точек оказания услуг
        $tradePoints = TradePoints::find(["subjectid = :userId: AND subjecttype = 0 AND deleted = true AND deletedcascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]], false);
        foreach ($tradePoints as $tradePoint) {
            $tradePoint->setTransaction($transaction);
            if (!$tradePoint->restore()) {
                $transaction->rollback(
                    "Невозможно восстановить точки оказания услуг"
                );
                return false;
            }
        }

        //каскадное восстановление новостей
        $news = News::find(["subjectid = :userId: AND subjecttype = 0 AND deleted = true AND deletedcascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]], false);
        foreach ($news as $new) {
            $new->setTransaction($transaction);
            if (!$new->restore()) {
                $transaction->rollback(
                    "Не удалось восстановить новости пользователя"
                );
                return false;
            }
        }

        //каскадное 'удаление' услуг
        $services = Services::find(["subjectid = :userId: AND subjecttype = 0 AND deleted = true AND deletedcascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]], false);
        foreach ($services as $service) {
            $service->setTransaction($transaction);
            if (!$service->restore()) {
                $transaction->rollback(
                    "Не удалось восстановить услуги пользователя"
                );
                return false;
            }
        }

        //каскадное восстановление запросов
        $requests = Requests::find(["subjectid = :userId: AND subjecttype = 0 AND deleted = true AND deletedcascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]], false);
        foreach ($requests as $request) {
            $request->setTransaction($transaction);
            if (!$request->restore()) {
                $transaction->rollback(
                    "Не удалось восстановить запросы пользователя"
                );
                return false;
            }
        }

        //каскадное восстановление заданий
        $tasks = Tasks::find(["subjectid = :userId: AND subjecttype = 0 AND deleted = true AND deletedcascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]], false);
        foreach ($tasks as $task) {
            $task->setTransaction($transaction);
            if (!$task->restore()) {
                $transaction->rollback(
                    "Не удалось восстановить задания пользователя"
                );
                return false;
            }
        }

        //каскадное восстановление предложений
        $offers = Offers::find(["subjectid = :userId: AND subjecttype = 0 AND deleted = true AND deletedcascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]], false);
        foreach ($offers as $offer) {
            $offer->setTransaction($transaction);
            if (!$offer->restore()) {
                $transaction->rollback(
                    "Не удалось восстановить предложения пользователя"
                );
                return false;
            }
        }

        $transaction->commit();
        return true;
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
     * @return array
     */
    public function getUserShortInfo(){
        $userInfo = $this->getRelated('Userinfo', [
            'columns' => Userinfo::shortColumns
        ]);

        if($userInfo)
            return $userInfo->toArray();

        return [
                'userid' => $this->user_id ,
                'email' => $this->email
            ];
    }

    /**
     * @param $id
     * @return bool
     */
    public static function isUserExist($id){
        $user = parent::findFirst($id);
        if(!$user)
            return false;
        return true;
    }
}
