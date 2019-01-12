<?php

namespace App\Services;

use App\Models\UserLocation;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class UserLocationService extends AbstractService
{
    const ADDED_CODE_NUMBER = 15000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_USER_LOCATION = 1 + self::ADDED_CODE_NUMBER;
    //const ERROR_NEWS_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_USER_LOCATION = 3 + self::ADDED_CODE_NUMBER;


    /**
     * Creating a new news
     *
     * @param array $newsData
     * @return News. If all ok, return News object
     */
    public function createNews(array $newsData)
    {
        $news = new News();

        $this->fillNews($news, $newsData);

        if ($news->create() == false) {
            $errors = SupportClass::getArrayWithErrors($news);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to create news',
                    self::ERROR_UNABLE_CREATE_NEWS, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to create news',
                    self::ERROR_UNABLE_CREATE_NEWS);
            }
        }

        return $news;
    }

    public function getNewsById(int $newsId)
    {
        $news = News::findFirstByNewsId($newsId);

        if (!$news) {
            throw new ServiceException('News don\'t exists', self::ERROR_NEWS_NOT_FOUND);
        }
        return $news;
    }

    public function fillNews(News $news, array $data)
    {
        if (!empty(trim($data['news_text'])))
            $news->setNewsText($data['news_text']);
        if (!empty(trim($data['title'])))
            $news->setTitle($data['title']);
        if (!empty(trim($data['publish_date'])))
            $news->setPublishDate(date('Y-m-d H:i:s', strtotime($data['publish_date'])));
        if (!empty(trim($data['account_id'])))
            $news->setAccountId($data['account_id']);
    }

    public function changeNews(News $news,array $newsData)
    {
        $this->fillNews($news, $newsData);

        if ($news->update() == false) {
            $errors = SupportClass::getArrayWithErrors($news);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to change news',
                    self::ERROR_UNABLE_CHANGE_NEWS, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to change news',
                    self::ERROR_UNABLE_CHANGE_NEWS);
            }
        }

        return $news;
    }
}
