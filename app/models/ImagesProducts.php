<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class ImagesProducts extends ImagesModel
{

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'object_id',
            new Callback(
                [
                    "message" => "Product doesn't exist",
                    "callback" => function ($image) {
                        $object = Products::findFirstByProductId($image->getObjectId());
                        if ($object)
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
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("images_products");
        $this->belongsTo('object_id', 'App\Models\Products', 'product_id', ['alias' => 'Products']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'images_products';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesProducts[]|ImagesProducts|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesProducts|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function delete($data = null, $whiteList = null)
    {
        $images = self::findAllImages(get_class(),$this->getObjectId());

        if(count($images)<2){
            $this->appendMessage('Unable to delete last image from product');
            return false;
        }

        $result = parent::delete($data, $whiteList);
        return $result;
    }
}
