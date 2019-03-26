<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class ImagesTasks extends ImagesModel
{

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'object_id',
            new Callback(
                [
                    "message" => "Такой отзыв не существует",
                    "callback" => function ($image) {
                        return $image->tasks?true:false;
                    }
                ]
            )
        );

        return parent::validation()&&$this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("images_tasks");
        $this->belongsTo('object_id', 'App\Models\Tasks', 'task_id', ['alias' => 'Tasks']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'images_tasks';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesTasks[]|ImagesTasks|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesTasks|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
