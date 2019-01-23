<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

use App\Libs\ImageLoader;
/**
 *  Model with basic functions for all models with images
 *
 * Class ImagesModel
 * @package App\Models
 */
abstract class ImagesModel extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $image_id;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    protected $image_path;

    const MAX_IMAGES = 10;
    const DEFAULT_RESULT_PER_PAGE = 10;

    /**
     * Method to set the value of field imageid
     *
     * @param integer $image_id
     * @return $this
     */
    public function setImageId($image_id)
    {
        $this->image_id = $image_id;

        return $this;
    }

    /**
     * Method to set the value of field imagepath
     *
     * @param string $image_path
     * @return $this
     */
    public function setImagePath($image_path)
    {
        $this->image_path = $image_path;

        return $this;
    }

    /**
     * Returns the value of field imageid
     *
     * @return integer
     */
    public function getImageId()
    {
        return $this->image_id;
    }

    /**
     * Returns the value of field imagepath
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->image_path;
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
            'image_path',
            new Callback(
                [
                    "message" => "Формат не поддерживается",
                    "callback" => function ($image) {
                        $format = pathinfo($image->getImagePath(), PATHINFO_EXTENSION);

                        if ($format == 'jpeg' || 'jpg')
                            return true;
                        elseif ($format == 'png')
                            return true;
                        elseif ($format == 'gif')
                            return true;
                        else {
                            return false;
                        }
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
    }

    /*public static function getComments($imageId)
    {
        $comments = CommentsImagesUsers::findByImageId($imageId);

        return $comments;
    }*/

    public function delete($delete = false, $data = null, $whiteList = null)
    {
        $path = $this->getImagePath();

        $result = parent::delete($delete, $data, $whiteList);

        if ($result && $path != null && $delete = true) {
            ImageLoader::delete($path);
        }

        return $result;
    }

    /**
     * @param /ResultSet $images
     * @return array
     */
    public static function handleImages($images)
    {
        $handledImages = [];
        foreach ($images as $image) {
            $handledImage = [
                'image_id' => $image->getImageId(),
                'image_path' => $image->getImagePath()];
            $handledImages[] = $handledImage;
        }
        return $handledImages;
    }

    public static function findImageById($imageId){
        return self::findFirstByImageId($imageId);
    }
}
