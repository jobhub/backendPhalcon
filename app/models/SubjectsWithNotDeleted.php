<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class SubjectsWithNotDeleted extends NotDeletedModelWithCascade
{
    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjectid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $subjecttype;

    /**
     * Method to set the value of field subjectId
     *
     * @param integer $subjectid
     * @return $this
     */
    public function setSubjectId($subjectid)
    {
        $this->subjectid = $subjectid;

        return $this;
    }

    public function setSubjectType($subjecttype)
    {
        $this->subjecttype = $subjecttype;

        return $this;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectId()
    {
        return $this->subjectid;
    }

    /**
     * Returns the value of field subjectId
     *
     * @return integer
     */
    public function getSubjectType()
    {
        return $this->subjecttype;
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
            'subjectid',
            new Callback(
                [
                    "message" => "Такой субъект не существует",
                    "callback" => function ($service) {
                        return SubjectsWithNotDeleted::checkSubjectExists($service->getSubjectId(), $service->getSubjectType());
                    }
                ]
            )
        );
        return $this->validate($validator);
    }

    public static function checkUserHavePermission($userId, $subjectId, $subjectType, $right = null)
    {
        $user = Users::findFirstByUserId($userId);

        if (!$user)
            return false;

        if ($subjectType == 1) {
            //субъект - компания

            if (!Companies::checkUserHavePermission($userId, $subjectId, $right)) {
                return false;
            }
            return true;
        } else if ($subjectType == 0) {
            //субъект - пользователь

            if ($subjectId != $userId && $user->getRole() != ROLE_MODERATOR) {
                return false;
            }

            return true;
        }

        return false;
    }

    public static function checkSubjectExists($subjectId, $subjectType)
    {
        if ($subjectType == 0) {
            $user = Users::findFirstByUserId($subjectId);
            if ($user)
                return true;
            return false;
        } else if ($subjectType == 1) {
            $company = Companies::findFirstByCompanyId($subjectId);
            if ($company)
                return true;
            return false;
        } else
            return false;
    }

    public static function equals($subjectId1, $subjectType1, $subjectId2, $subjectType2)
    {
        if($subjectId1 == $subjectId2 && $subjectType1 == $subjectType2)
            return true;
        return false;
    }

    public static function findBySubject($subjectId,$subjectType, $order = null)
    {
        //if($order!= null)
        return parent::find(['subjectId = :subjectId: AND subjecttype = :subjectType:',
            'bind' => ['subjectId' => $subjectId, 'subjectType' => $subjectType],
            'order' => $order]);
    }
}