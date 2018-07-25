<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;


use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class Users extends NotDeletedModelWithCascade
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userId;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    protected $email;

    /**
     *
     * @var integer
     * @Column(type="integer", nullable=false)
     */
    protected $phoneId;

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
     * @param integer $phoneId
     * @return $this
     */
    public function setPhoneId($phoneId)
    {
        $this->phoneId = $phoneId;

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
        $this->password = sha1($password);

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
        return $this->userId;
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
        return $this->phoneId;
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

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model'   => $this,
                    'message' => 'Введите, пожалуйста, правильный адрес',
                ]
            )
        );

        $validator->add(
            'phoneId',
            new Callback(
                [
                    "message" => "Телефон не был создан",
                    "callback" => function($user) {
                        $phone = Phones::findFirstByPhoneId($user->getPhoneId());

                        if($phone)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'password',
            new Callback(
                [
                    "message" => "Пароль должен содержать не менее 6 символов",
                    "callback" => function($user) {

                        if($user->getPassword()!= null && strlen($user->getPassword()) >= 6)
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
        $this->hasMany('userId', 'Favoritecategories', 'userId', ['alias' => 'Favoritecategories']);
        $this->hasMany('userId', 'Logs', 'userId', ['alias' => 'Logs']);
        $this->hasOne('userId', 'Userinfo', 'userId', ['alias' => 'Userinfo']);
        $this->belongsTo('phoneId', 'Phones', 'phoneId', ['alias' => 'Phones']);
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

    public function delete($delete = false, $data = null, $whiteList = null)
    {
        try {
            // Создаем менеджера транзакций
            $manager = new TxManager();
            // Запрос транзакции
            $transaction = $manager->get();
            $this->setTransaction($transaction);

            if (!$delete) {

                //каскадное 'удаление' новостей
                $news = News::find(["subjectId = :userId: ANd newType = 0",
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
                $services = Services::find(["subjectId = :userId: AND subjectType = 0",
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
                $requests = Requests::find(["subjectId = :userId: AND subjectType = 0",
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
                $tasks = Tasks::find(["subjectId = :userId: AND subjectType = 0",
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
                $offers = Offers::find(["subjectId = :userId: AND subjectType = 0",
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

                $result = parent::delete($delete,false, $data, $whiteList);

                if (!$result) {
                    $transaction->rollback(
                        "Невозможно удалить пользователя"
                    );
                    return $result;
                }

                $transaction->commit();
                return true;
            } else {
                $result = parent::delete($delete,false, $data, $whiteList);
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
        if(!parent::restore()){
            $transaction->rollback(
                "Невозможно восстановить пользователя"
            );
            return false;
        }

        //каскадное восстановление новостей
        $news = News::find(["subjectId = :userId: AND newType = 0 AND deleted = true AND deletedCascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]],false);
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
        $services = Services::find(["subjectId = :userId: AND subjectType = 0 AND deleted = true AND deletedCascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]],false);
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
        $requests = Requests::find(["subjectId = :userId: AND subjectType = 0 AND deleted = true AND deletedCascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]],false);
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
        $tasks = Tasks::find(["subjectId = :userId: AND subjectType = 0 AND deleted = true AND deletedCascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]],false);
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
        $offers = Offers::find(["subjectId = :userId: AND subjectType = 0 AND deleted = true AND deletedCascade = true",
            'bind' =>
                ['userId' => $this->getUserId()
                ]],false);
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
                'userId' => "$this->userId",
            ]
        );
        $count=$count[0]['c'];
        return $count;
    }
    public function getRatingForCategory($idCategory)
    {
        $query=$this->getModelsManager()->createQuery('SELECT AVG(reviews.raiting) AS a FROM reviews, auctions, tasks, users WHERE tasks.categoryId=:categoryId: AND tasks.taskId=auctions.taskId AND auctions.auctionId=reviews.auctionId AND reviews.userId_object=:userId: AND reviews.executor=1');
        $avg = $query->execute(
            [
                'userId' => "$this->userId",
                'categoryId'=>$idCategory
            ]
        );
        $avg=$avg[0]['a'];
        return $avg;
    }
}
