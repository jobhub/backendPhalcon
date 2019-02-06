<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesUsers extends ImagesModel
{
    protected $image_text;

    protected $likes;

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
     * @return mixed
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param mixed $likes
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
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
            'object_id',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($image) {
                        $user = Users::findFirstByUserId($image->getObjectId());
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
        $this->setSource("image_susers");
        $this->belongsTo('object_id', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'images_users';
    }

    public function getSequenceName()
    {
        return "imagesusers_image_id_seq";
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

            $userinfo = Userinfo::findFirstByUserId($this->getObjectId());

            if ($userinfo->getPathToPhoto() == $path) {
                $userinfo->setPathToPhoto(null);
                $userinfo->update();
            }
        }

        return $result;
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
            self::find(['conditions'=>'object_id = :user_id:','bind'=>['user_id'=>$userId],
                'limit'=>$page_size,'offset'=>$offset,'order'=>'image_id desc'])->toArray()
        );
    }

    public static function handleImage($image, Accounts $account = null)
    {
        $handledImage = [
            'image_id' => $image['image_id'],
            'image_path' => $image['image_path']];

        if($account!=null) {
            $relatedAccounts = $account->getRelatedAccounts();
            $accountId = $account->getId();
        }

        $handledImage['stats']['comments'] = CommentsModel::getCountOfComments('comments_imagesusers', $image['image_id']);
        $handledImage = ForwardsInNewsModel::handleObjectWithForwards('App\Models\ForwardsImagesUsers',$handledImage, $image['image_id'], $relatedAccounts);

        $handledImage = LikeModel::handleObjectWithLikes($handledImage,$image,$accountId);

        $handledImage['image_text'] = $image['image_text'];

        return $handledImage;
    }

    public static function handleImages($images)
    {
        $session = DI::getDefault()->get('session');
        $accountId = $session->get('accountId');

        $account = Accounts::findFirstById($accountId);

        if(!$account)
            $account = null;

        $handledImages = [];
        foreach ($images as $image) {
            $handledImages[] = self::handleImage($image,$account);
        }
        return $handledImages;
    }
}
