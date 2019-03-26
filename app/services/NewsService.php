<?php

namespace App\Services;

use App\Models\News;

use App\Libs\SupportClass;
use App\Models\NewsInfo;

use Phalcon\DI\FactoryDefault as DI;

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
        try {
            $di = DI::getDefault();
            $db = $di->getDb();
            $db->begin();

            $news = new News();

            if($newsData['news_type'] == News::NEWS_TYPE_FORWARD_NEWS){
                $related_news = $this->getNewsById($newsData['related_id']);

                if($related_news->getRelatedId()!=null){
                    $newsData['related_id'] = $related_news->getRelatedId();
                    $newsData['news_type'] = $related_news->getNewsType();
                }

                $news_forward_data['forwards_count'] = $related_news->getForwardsCount()+1;

                $this->changeNews($related_news,$news_forward_data);
            }

            $this->fillNews($news, $newsData);

            if ($news->create() == false) {
                $db->rollback();
                SupportClass::getErrorsWithException($news,self::ERROR_UNABLE_CREATE_NEWS,'unable to create news');
            }

            if($news->getNewsType()<10){
                $news_info = new NewsInfo();

                $news_info->setTitle($newsData['title']);
                $news_info->setNewsId($news->getNewsId());

                if (!$news_info->create()) {
                    $db->rollback();
                    SupportClass::getErrorsWithException($news_info,self::ERROR_UNABLE_CREATE_NEWS,'unable to create news info');
                }
            }

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        $db->commit();

        return $news;
    }

    public function getNewsById($newsId)
    {
        $news = News::findFirstByNewsId($newsId);

        if (!$news) {
            throw new ServiceException('News don\'t exists', self::ERROR_NEWS_NOT_FOUND);
        }
        return $news;
    }

    public function fillNews(News $news, array $data)
    {
        if (isset($data['news_text']))
            $news->setNewsText($data['news_text']);
        if (!empty(trim($data['publish_date'])))
            $news->setPublishDate(date('Y-m-d H:i:sO', strtotime($data['publish_date'])));
        if (!empty(trim($data['account_id'])))
            $news->setAccountId($data['account_id']);
        if (isset($data['news_type']))
            $news->setNewsType($data['news_type']);
        if (isset($data['related_id']))
            $news->setRelatedId($data['related_id']);
        if (isset($data['forwards_count']))
            $news->setForwardsCount($data['forwards_count']);
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
        try {
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
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $news;
    }

    public function changeNews(News $news, array $newsData)
    {
        try {
            $di = DI::getDefault();
            $db = $di->getDb();
            $db->begin();

            $this->fillNews($news, $newsData);

            if ($news->update() == false) {
                $db->rollback();
                SupportClass::getErrorsWithException($news,self::ERROR_UNABLE_CHANGE_NEWS,'unable to change news');
            }

            if(isset($newsData['title']) && $news->getNewsType() < 10){
                $news_info = $news->NewsInfo;

                $news_info->setTitle($newsData['title']);

                if ($news_info->update() == false) {
                    $db->rollback();
                    SupportClass::getErrorsWithException($news_info,self::ERROR_UNABLE_CHANGE_NEWS,'unable to change news info');
                }
            }

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        $db->commit();

        return $news;
    }
}
