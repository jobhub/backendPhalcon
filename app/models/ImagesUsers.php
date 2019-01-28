<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

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

    protected $image_text;

    const MAX_IMAGES = 10;

    /**
     * @return mixed
     */
    public function getImageText()
    {
        return $this->image_text;
    }

    /**
     * @param mixed $image_text
     */
    public function setImageText($image_text)
    {
        $this->image_text = $image_text;
    }

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
        $this->belongsTo('user_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
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
        return self::handleImages($images->toArray());
    }

    /**
     * return non formatted images objects
     * @param $userId
     * @param $page
     * @param $page_size
     * @return mixed
     */
    public static function findImagesForUser($userId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE){
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        return self::handleImages(
            self::find(['conditions'=>'user_id = :user_id:','bind'=>['user_id'=>$userId],
                'limit'=>$page_size,'offset'=>$offset,'order'=>'image_id desc'])->toArray()
        );
    }

    public static function handleImages($images)
    {
        $session = DI::getDefault()->get('session');
        $accountId = $session->get('accountId');

        $handledImages = [];
        foreach ($images as $image) {
            $handledImage = [
                'image_id' => $image['image_id'],
                'image_path' => $image['image_path']];

            $handledImage['stats']['comments'] = count(CommentsImagesUsers::findByObjectId($handledImage['image_id']));

            $handledImage = LikeModel::handleObjectWithLikes($handledImage,$image,$accountId);

            $handledImage['image_text'] = $image['image_text'];
            $handledImages[] = $handledImage;
        }
        return $handledImages;
    }
}
