<?php
namespace App\Models;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesTemp extends ImagesModel
{

    protected $further_path;

    /**
     * @return mixed
     */
    public function getFurtherPath()
    {
        return $this->further_path;
    }

    /**
     * @param mixed $further_path
     */
    public function setFurtherPath($further_path)
    {
        $this->further_path = $further_path;
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
                    "message" => "Такой аккаунт не существует",
                    "callback" => function ($image) {
                        $account = Accounts::findFirstById($image->getObjectId());
                        if ($account)
                            return true;
                        return false;
                    }
                ]
            )
        );


        return parent::validation() && $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("images_temp");
        $this->belongsTo('object_id', 'App\Models\Accounts', 'id', ['alias' => 'Accounts']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'images_temp';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesNews[]|ImagesNews|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesNews|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function handleImages($images)
    {
        $handledImages = [];
        foreach ($images as $image) {
            $handledImages[] = self::handleImage($image);
        }
        return $handledImages;
    }

    public static function handleImage($image)
    {
        $handledImage = [
            'image_id' => $image['image_id'],
            'image_path' => $image['image_path'],
            /*'further_path' => $image['further_path']*/];
        return $handledImage;
    }
}
