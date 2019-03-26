<?php

namespace App\Models;

class NewsInfo extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $news_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * Method to set the value of field news_id
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
     * Method to set the value of field title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the value of field news_id
     *
     * @return integer
     */
    public function getNewsId()
    {
        return $this->news_id;
    }

    /**
     * Returns the value of field title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }



    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("news_info");
        $this->hasOne('news_id', 'App\Models\News', 'news_id', ['alias' => 'News']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'news_info';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return NewsInfo[]|NewsInfo|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return NewsInfo|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findById($id, array $columns = null)
    {
        if ($columns == null)
            return self::findFirst(['news_id = :id:',
                'bind' => ['id' => $id]]);
        else {
            return self::findFirst(['columns' => $columns, 'news_id = :id:',
                'bind' => ['id' => $id]]);
        }
    }
}
