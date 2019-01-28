<?php

namespace App\Services;

use App\Models\News;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class NewsService extends AbstractService
{
    const ADDED_CODE_NUMBER = 8000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_NEWS = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_NEWS_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_NEWS = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_NEWS = 4 + self::ADDED_CODE_NUMBER;


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
            $news->setPublishDate(date('Y-m-d H:i:sO', strtotime($data['publish_date'])));
        if (!empty(trim($data['account_id'])))
            $news->setAccountId($data['account_id']);
    }

    //Чертов php не позволяет переопределять методы, используя разные входные переменные.
    //Бесит.
    /*public function deleteNews(int $newsId)
    {
        $news = $this->getNewsById($newsId);

        if ($news->delete() == false) {
            $errors = SupportClass::getArrayWithErrors($news);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to delete news',
                    self::ERROR_UNABLE_DELETE_NEWS, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to delete news',
                    self::ERROR_UNABLE_DELETE_NEWS);
            }
        }

        return $news;
    }*/

    public function deleteNews(News $news)
    {
        if ($news->delete() == false) {
            $errors = SupportClass::getArrayWithErrors($news);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to delete news',
                    self::ERROR_UNABLE_DELETE_NEWS, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to delete news',
                    self::ERROR_UNABLE_DELETE_NEWS);
            }
        }

        return $news;
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
