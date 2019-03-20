<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\Events;
use App\Models\News;

use App\Libs\SupportClass;
use App\Models\Statistics;
use App\Models\Tags;
use Composer\Script\Event;

/**
 * business logic for users
 *
 * Class UsersService
 */
class EventService extends AbstractService
{
    const ADDED_CODE_NUMBER = 39000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_EVENT = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_EVENT_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_EVENT = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_EVENT = 4 + self::ADDED_CODE_NUMBER;

    /**
     * Creating a new news
     *
     * @param array $eventData
     * @return Events. If all ok, return News object
     */
    public function createEvent(array $eventData)
    {
        try {

            if (!(isset($eventData['images']) && is_array($eventData['images']))) {
                throw new ServiceExtendedException('Unable to create event without images', self::ERROR_UNABLE_CREATE_EVENT);
            }

            $event = new Events();
            $statistics = $this->createStatistics();

            $eventData['statistics_id'] = $statistics->getStatisticsId();
            $this->fillEvent($event, $eventData);


            if ($event->create() == false) {
                $errors = SupportClass::getArrayWithErrors($event);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('unable to create event',
                        self::ERROR_UNABLE_CREATE_EVENT, null, null, $errors);
                else {
                    throw new ServiceExtendedException('unable to create event',
                        self::ERROR_UNABLE_CREATE_EVENT);
                }
            }

            if (isset($eventData['tags']) && is_array($eventData['tags'])) {
                foreach ($eventData['tags'] as $tag) {
                    if (is_string($tag))
                        $this->tagService->addTagToObject($tag, $event->getEventId(), TagService::TYPE_EVENT);
                }
            }

            $ids = $this->imageService->createImagesToObject($eventData['images'], $event, ImageService::TYPE_EVENT);

            $this->imageService->saveImagesToObject($eventData['images'], $event, $ids, ImageService::TYPE_EVENT);


        } catch (\PDOException $e) {
            echo $e;
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $event;
    }

    public function createStatistics()
    {
        try {
            $statistics = new Statistics();

            if (!$statistics->create()) {
                $errors = SupportClass::getArrayWithErrors($statistics);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('unable to create event',
                        self::ERROR_UNABLE_CREATE_EVENT, null, null, $errors);
                else {
                    throw new ServiceExtendedException('unable to create event',
                        self::ERROR_UNABLE_CREATE_EVENT);
                }
            }

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $statistics;
    }

    public function incrementStatistics(Statistics $stats, array $statsData)
    {
        try {

            if (isset($statsData['average_display_time']) &&
                isset($statsData['number_of_display']) && SupportClass::checkInteger($statsData['number_of_display'])) {

                $newAverage = ($stats->getAverageDisplayTime() * $stats->getNumberOfDisplay() +
                        $statsData['average_display_time'] * $statsData['number_of_display']) /
                    ($stats->getNumberOfDisplay() + $statsData['number_of_display']);

                $stats->setAverageDisplayTime($newAverage);

                $stats->setNumberOfDisplay($stats->getNumberOfDisplay() + $statsData['number_of_display']);
            }

            if (isset($statsData['number_of_clicks']) && SupportClass::checkInteger($statsData['number_of_clicks']))
                $stats->setNumberOfClicks($stats->getNumberOfClicks() + $statsData['number_of_clicks']);


            if ($stats->update() == false) {
                $errors = SupportClass::getArrayWithErrors($stats);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('unable to change event',
                        self::ERROR_UNABLE_CHANGE_EVENT, null, null, $errors);
                else {
                    throw new ServiceExtendedException('unable to change event',
                        self::ERROR_UNABLE_CHANGE_EVENT);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $stats;
    }

    public function getEventById($eventId)
    {
        $news = Events::findById($eventId);

        if (!$news) {
            throw new ServiceException('Event does not exists', self::ERROR_EVENT_NOT_FOUND);
        }
        return $news;
    }

    public function fillEvent(Events $event, array $data)
    {
        if (isset($data['name']))
            $event->setName($data['name']);
        if (isset($data['description']))
            $event->setDescription($data['description']);

        if (!empty(trim($data['date_publication'])))
            $event->setDatePublication(date('Y-m-d H:i:sO', strtotime($data['date_publication'])));

        if (!empty($data['center_marker_id']))
            $event->setCenterMarkerId($data['center_marker_id']);
        else {
            if (isset($data['longitude']) && isset($data['latitude'])) {
                $marker = $this->markerService->createMarker($data['longitude'], $data['latitude']);

                $event->setCenterMarkerId($marker->getMarkerId());
            }
        }

        if (isset($data['radius']))
            $event->setRadius($data['radius']);

        if (isset($data['active']))
            $event->setActive($data['active']);

        if (!empty(trim($data['statistics_id'])))
            $event->setStatisticsId($data['statistics_id']);

        if (!empty(trim($data['account_id'])))
            $event->setAccountId($data['account_id']);

        if (!empty(trim($data['binder_id'])))
            $event->setBinderId($data['binder_id']);

        if (!empty(trim($data['service_type'])) &&
            ($data['service_type'] == 'product' || $data['service_type'] == 'service'))
            $event->setServiceType($data['service_type']);
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

    public function deleteEvent(Events $events)
    {
        try {
            if ($events->delete() == false) {
                $errors = SupportClass::getArrayWithErrors($events);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('unable to delete event',
                        self::ERROR_UNABLE_DELETE_EVENT, null, null, $errors);
                else {
                    throw new ServiceExtendedException('unable to delete event',
                        self::ERROR_UNABLE_DELETE_EVENT);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $events;
    }

    public function changeEvent(Events $event, array $eventData)
    {
        try {
            $this->fillEvent($event, $eventData);

            if ($event->update() == false) {
                $errors = SupportClass::getArrayWithErrors($event);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('unable to change event',
                        self::ERROR_UNABLE_CHANGE_EVENT, null, null, $errors);
                else {
                    throw new ServiceExtendedException('unable to change event',
                        self::ERROR_UNABLE_CHANGE_EVENT);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $event;
    }
}
