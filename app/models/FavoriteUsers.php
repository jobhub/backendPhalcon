<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavoriteUsers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $user_subject;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $user_object;

    /**
     * Method to set the value of field userSubject
     *
     * @param integer $user_subject
     * @return $this
     */
    public function setUserSubject($user_subject)
    {
        $this->user_subject = $user_subject;

        return $this;
    }

    /**
     * Method to set the value of field userObject
     *
     * @param integer $user_object
     * @return $this
     */
    public function setUserObject($user_object)
    {
        $this->user_object = $user_object;

        return $this;
    }

    /**
     * Returns the value of field userSubject
     *
     * @return integer
     */
    public function getUserSubject()
    {
        return $this->user_subject;
    }

    /**
     * Returns the value of field userObject
     *
     * @return integer
     */
    public function getUserObject()
    {
        return $this->user_object;
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
            'user_object',
            new Callback(
                [
                    "message" => "Пользователь для подписки не существует",
                    "callback" => function($favUser) {
                        $user = Users::findFirstByUserId($favUser->getUserObject());

                        if($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'user_subject',
            new Callback(
                [
                    "message" => "Пользователь подписчик не существует",
                    "callback" => function($favUser) {
                        if($favUser->getUserSubject() == $favUser->getUserObject())
                            return false;

                        $user = Users::findFirstByUserId($favUser->getUserSubject());

                        if($user)
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
        $this->setSource("favoriteUsers");
        $this->belongsTo('user_object', 'App\Models\Users', 'user_id', ['alias' => 'UserObject']);
        $this->belongsTo('user_subject', 'App\Models\Users', 'user_id', ['alias' => 'UserSubject']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favoriteUsers';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoriteusers[]|Favoriteusers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoriteusers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($userIdObject,$userIdSubject)
    {
        return FavoriteUsers::findFirst(["user_object = :userIdObject: AND user_subject = :userIdSubject:",
            "bind" => [
                "userIdObject" => $userIdObject,
                "userIdSubject" => $userIdSubject,
            ]
        ]);
    }
}
