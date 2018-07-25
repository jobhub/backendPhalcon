<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

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
                        if($task->getSubjectType() == 0) {
                            $user = Users::findFirstByUserId($task->getSubjectId());
                            if ($user)
                                return true;
                            return false;
                        } else if($task->getSubjectType() == 1){
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

        if ($offer->getSubjectType() == 1) {
            //Предложение компании
            $rightCompany = 'Offers';
            if($right == 'delete')
                $rightCompany = 'deleteOffer';
            else if($right == 'get')
                $rightCompany = 'getOffers';
            else if($right == 'edit')
                $rightCompany = 'editOffers';

            if (!Companies::checkUserHavePermission($userId, $offer->getSubjectId(), $rightCompany)) {
                return false;
            }
            return true;
        } else if ($offer->getSubjectType() == 0) {
            if ($offer->getSubjectId() != $userId && $user->getRole() != ROLE_MODERATOR) {
                return false;
            }
            return true;
        }
        return false;
    }

}
