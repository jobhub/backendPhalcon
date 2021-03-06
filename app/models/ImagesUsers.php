<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesUsers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $imageid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    protected $imagepath;

    const MAX_IMAGES = 10;

    /**
     * Method to set the value of field imageid
     *
     * @param integer $imageid
     * @return $this
     */
    public function setImageId($imageid)
    {
        $this->imageid = $imageid;

        return $this;
    }

    /**
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserId($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Method to set the value of field imagepath
     *
     * @param string $imagepath
     * @return $this
     */
    public function setImagePath($imagepath)
    {
        $this->imagepath = $imagepath;

        return $this;
    }

    /**
     * Returns the value of field imageid
     *
     * @return integer
     */
    public function getImageId()
    {
        return $this->imageid;
    }

    /**
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userid;
    }

    /**
     * Returns the value of field imagepath
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagepath;
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
            'userid',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($image) {
                        $user = Users::findFirstByUserid($image->getUserId());
                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'imagepath',
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

    public static function getComments($imageId)
    {
        /*$comments = [];
        for ($i = 0; $i < 6; $i++) {
            $type = rand(0, 2);
            if ($type == 0) {
                $comment = ['commenttext' => 'оооооооооооооооооооооооочень хочу отдыхать трам парам там там там пам',
                    'commentdate' => '2018-09-15 10:23:54+00', 'commentid' => $i + 1,
                ];
            } else if ($type == 1) {
                $comment = ['commenttext' => 'оооооооооооооооооооооооочень хочу отдыхать НУ ПРЯМ ХОЧУ НЕ МОГУ',
                    'commentdate' => '2018-09-15 10:23:54+00', 'commentid' => $i + 1,'replyid'  => ($i-1)>0?($i-1):0
                ];
            } else if ($type == 2) {
                $comment = ['commenttext' => 'оооооооооооооооооооооооочень хочу отдыхать ОТПУСТИТЕ МЕНЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯЯ',
                    'commentdate' => '2018-09-15 10:23:54+00', 'commentid' => $i + 1,
                ];
            }
            $comment['likes'] = ($i*(int)(7/5))+$i*7%6;

            $comment['publisherUser'] = ['userid' => '9', 'email' => 'eenotova@mail.ru',
                'phone' => '+7 954 352-65-75', 'firstname' => 'Екатерина',
                'lastname' => 'Енотова', 'patronymic' => "Васильевна",
                'lasttime' => '2019-09-08 16:00:30+00', 'male' => '0',
                'birthday' => '1997-05-25 00:00:00+00', 'pathtophoto' => 'images/profile/user/1.jpg',
                'status' => null];

            $comments[] = $comment;
        }*/

        $comments = CommentsImagesUsers::findByImageId($imageId);

        return $comments;
    }

    public function delete($delete = false, $data = null, $whiteList = null)
    {
        $path = $this->getImagePath();

        $result = parent::delete($delete, false, $data, $whiteList);

        if ($result && $path != null && $delete = true) {
            ImageLoader::delete($path);

            $userinfo = Userinfo::findFirstByUserid($this->getUserId());
            if ($userinfo->getPathToPhoto() == $path) {
                $userinfo->setPathToPhoto(null);

                $userinfo->update();
            }
        }

        return $result;
    }

    public static function getImages($userId)
    {
        $images = ImagesUsers::findByUserid($userId);
        return self::handleImages($images);
    }

    public static function handleImages($images)
    {
        $handledImages = [];
        foreach ($images as $image) {
            $handledImage = [
                'imageid' => $image->getImageId(),
                'imagepath' => $image->getImagePath()];

            $handledImage['stats'] = new Stats();
            $handledImage['comments'] = CommentsImagesUsers::getComments($image->getImageId());
            $handledImage['stats']->setComments(count($handledImage['comments']));
            $handledImages[] = $handledImage;
        }
        return $handledImages;
    }
}
