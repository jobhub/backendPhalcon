<?php
namespace App\Models;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesNews extends ImagesModel
{
    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $news_id;


    const MAX_IMAGES = 3;

    /**
     * Method to set the value of field newid
     *
     * @param integer $news_id
     * @return $this
     */
    public function setNewsId($news_id)
    {
        $this->news_id = $news_id;

        return $this;
    }

    /**
     * Returns the value of field newid
     *
     * @return integer
     */
    public function getNewsId()
    {
        return $this->news_id;
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
            'news_id',
            new Callback(
                [
                    "message" => "Такая новость не существует",
                    "callback" => function ($image) {
                        $new = News::findFirstByNewsId($image->getNewsId());
                        if ($new)
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
        $this->setSource("imagesnews");
        $this->belongsTo('news_id', 'App\Models\News', 'news_id', ['alias' => 'News']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'imagesnews';
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
     * @return mixed
     */
    public static function findImagesForNews($newsId){
        return self::findByNewsId($newsId);
    }
}
