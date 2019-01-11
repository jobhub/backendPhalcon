<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesUsers extends ImagesModel
{
    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $user_id;

    const MAX_IMAGES = 10;

    /**
     * Method to set the value of field userid
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
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
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
            'user_id',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($image) {
                        $user = Users::findFirstByUserId($image->getUserId());
                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        return parent::validation()&& $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("imagesusers");
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'imagesusers';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesUsers[]|ImagesUsers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesUsers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function delete($delete = false, $data = null, $whiteList = null)
    {
        $path = $this->getImagePath();

        $result = parent::delete($delete, $data, $whiteList);

        if ($result && $path != null && $delete = true) {
            $userinfo = Userinfo::findFirstByUserId($this->getUserId());
            if ($userinfo->getPathToPhoto() == $path) {
                $userinfo->setPathToPhoto(null);
                $userinfo->update();
            }
        }

        return $result;
    }

    /**
     * return formatted array with images
     * @param $userId
     * @return array
     */
    public static function getImages($userId)
    {
        $images = ImagesUsers::findByUserId($userId);
        return self::handleImages($images);
    }

    /**
     * return non formatted images objects
     * @param $userId
     * @return mixed
     */
    public static function findImagesForUser($userId){
        return self::findByUserId($userId);
    }

    public static function handleImages($images)
    {
        $handledImages = [];
        foreach ($images as $image) {
            $handledImage = [
                'image_id' => $image->getImageId(),
                'image_path' => $image->getImagePath()];

            $handledImage['stats'] = new Stats();
            $handledImage['comments'] = CommentsImagesUsers::getComments($image->getImageId());
            $handledImage['stats']->setComments(count($handledImage['comments']));
            $handledImages[] = $handledImage;
        }
        return $handledImages;
    }
}
