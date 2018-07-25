<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Mvc\Model\Message;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class Offers extends NotDeletedModelWithCascade
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $offerId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $taskId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $deadline;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $price;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $selected;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $confirmed;


    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectType;

    /**
     * Method to set the value of field offerId
     *
     * @param integer $offerId
     * @return $this
     */
    public function setOfferId($offerId)
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * Method to set the value of field subjectId
     *
     * @param integer $subjectId
     * @return $this
     */
    public function setSubjectId($subjectId)
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function setSubjectType($subjectType)
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    /**
     * Method to set the value of field taskId
     *
     * @param integer $taskId
     * @return $this
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * Method to set the value of field deadline
     *
     * @param string $deadline
     * @return $this
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;

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
     * Method to set the value of field price
     *
     * @param integer $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Method to set the value of field selected
     *
     * @param string $selected
     * @return $this
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Method to set the value of field confirmed
     *
     * @param string $confirmed
     * @return $this
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Returns the value of field offerId
     *
     * @return integer
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectType()
    {
        return $this->subjectType;
    }

    /**
     * Returns the value of field taskId
     *
     * @return integer
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * Returns the value of field deadline
     *
     * @return string
     */
    public function getDeadline()
    {
        return $this->deadline;
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
     * Returns the value of field price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the value of field selected
     *
     * @return string
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Returns the value of field confirmed
     *
     * @return string
     */
    public function getConfirmed()
    {
        return $this->confirmed;
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
            'subjectId',
            new Callback(
                [
                    "message" => "Такой субъект не существует",
                    "callback" => function ($task) {
                        if ($task->getSubjectType() == 0) {
                            $user = Users::findFirstByUserId($task->getSubjectId());
                            if ($user)
                                return true;
                            return false;
                        } else if ($task->getSubjectType() == 1) {
                            $company = Companies::findFirstByCompanyId($task->getSubjectId());
                            if ($company)
                                return true;
                            return false;
                        } else
                            return false;
                    }
                ]
            )
        );

        $validator->add(
            'taskId',
            new Callback(
                [
                    "message" => "Задание не существует",
                    "callback" => function ($offer) {
                        $task = Tasks::findFirstByTaskId($offer->getTaskId());

                        if ($task)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'deadline',
            new PresenceOf(
                [
                    "message" => "Дата завершения задания должна быть указана",
                ]
            )
        );

        $validator->add(
            'price',
            new PresenceOf(
                [
                    "message" => "Цена должна быть указана",
                ]
            )
        );

        $validator->add(
            'confirmed',
            new Callback(
                [
                    "message" => "Предложение не может быть подтверждено, пока оно не выбрано заказчиком",
                    "callback" => function ($offer) {
                        if ($offer->getConfirmed() && !$offer->getSelected())
                            return false;
                        return true;
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
        $this->setSource("offers");
        $this->belongsTo('taskId', '\Tasks', 'taskId', ['alias' => 'Tasks']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'offers';
    }

    public static function checkUserHavePermission($userId, $offerId, $right = null)
    {
        $offer = Offers::findFirstByOfferId($offerId);
        $user = Users::findFirstByUserId($userId);

        if (!$offer)
            return false;

        return Subjects::checkUserHavePermission($userId, $offer->getSubjectId(), $offer->getSubjectType(), $right);
    }

    /**
     * Подтверждает готовность исполнителя выполнить заказ
     */
    public function confirm()
    {
        $task = Tasks::findFirstByTaskId($this->getTaskId());

        if ($task && $task->getStatus() == STATUS_WAITING_CONFIRM) {
            if ($this->getSelected()) {
                try {
                    // Создаем менеджера транзакций
                    $manager = new TxManager();
                    // Запрос транзакции
                    $transaction = $manager->get();
                    $this->setTransaction($transaction);

                    $this->setConfirmed(true);

                    if (!$this->update()) {
                        $transaction->rollback(
                            "Невозможно подтвердить предложение"
                        );
                        return false;
                    }

                    $task->setStatus(STATUS_EXECUTING);

                    if (!$task->update()) {
                        $transaction->rollback(
                            "Не удалось изменить статус задания"
                        );
                        return false;
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
            }
            $message = new Message(
                "Это предложение не выбрано"
            );

            $this->appendMessage($message);
            return false;
        }
        $message = new Message(
            "Нельзя подтвердить готовность выполнять задание в текущем состоянии"
        );

        $this->appendMessage($message);
        return false;
    }

    /**
     * Исполнитель отказывается от своего первоначального намерения выполнить заказ
     */
    public function reject()
    {
        $task = Tasks::findFirstByTaskId($this->getTaskId());

        if ($task && ($task->getStatus() == STATUS_WAITING_CONFIRM ||
                $task->getStatus() == STATUS_EXECUTING || $task->getStatus() == STATUS_EXECUTED_EXECUTOR ||
                $task->getStatus() == STATUS_EXECUTED_CLIENT)) {
            if ($this->getSelected()) {
                try {
                    // Создаем менеджера транзакций
                    $manager = new TxManager();
                    // Запрос транзакции
                    $transaction = $manager->get();
                    $this->setTransaction($transaction);

                    $this->setConfirmed(false);

                    if (!$this->update()) {
                        $transaction->rollback(
                            "Невозможно подтвердить предложение"
                        );
                        return false;
                    }

                    if($task->getStatus() == STATUS_WAITING_CONFIRM) {
                        $task->setStatus(STATUS_NOT_CONFIRMED);

                        if (strtotime(date("'Y-m-d H:i:s'")) < strtotime($task->getDateEnd())) {
                            $task->setStatus(STATUS_ACCEPTING);
                        }
                    } else{
                        $task->setStatus(STATUS_NOT_EXECUTED);
                    }

                    if (!$task->update()) {
                        $transaction->rollback(
                            "Не удалось изменить статус задания"
                        );
                        return false;
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
            }
            return false;
        }
        return false;
    }

}
