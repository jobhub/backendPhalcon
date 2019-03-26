<?php
namespace App\Models;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesNews extends ImagesModel
{

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
                    "message" => "Такая новость не существует",
                    "callback" => function ($image) {
                        $news_info = NewsInfo::findById($image->getObjectId());
                        if ($news_info)
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
        $this->setSource("images_news");
        $this->belongsTo('object_id', 'App\Models\NewsInfo', 'news_id', ['alias' => 'News']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'images_news';
    }

    public function getSequenceName()
    {
        return "imagesnews_image_id_seq";
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

    /**
     * return non formatted images objects
     * @param $newsId
     * @param $page
     * @param $page_size
     * @return mixed
     */
    public static function findImagesForNews($newsId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE){
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) *$page_size;
        return self::handleImages(
            self::find(['conditions'=>'object_id = :newsId:','bind'=>['newsId'=>$newsId],
                'limit'=>$page_size,'offset'=>$offset])->toArray()
        );
    }
}
