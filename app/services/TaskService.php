<?php

namespace App\Services;

use Phalcon\DI\FactoryDefault as DI;

use App\Models\Offers;
use App\Models\Tasks;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class TaskService extends AbstractService
{
    const ADDED_CODE_NUMBER = 17000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_TASK = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_TASK_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_TASK = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_TASK = 4 + self::ADDED_CODE_NUMBER;


    /**
     * Creating a new news
     *
     * @param array $taskData
     * @return Tasks. If all ok, return News object
     */
    public function createTask(array $taskData)
    {
        $task = new Tasks();

        $this->fillTask($task, $taskData);

        if ($task->create() == false) {
            SupportClass::getErrorsWithException($task,self::ERROR_UNABLE_CREATE_TASK,'Unable to create task');
            /*$errors = SupportClass::getArrayWithErrors($tasks);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to create task',
                    self::ERROR_UNABLE_CREATE_TASK, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to create task',
                    self::ERROR_UNABLE_CREATE_TASK);
            }*/
        }

        return $task;
    }

    public function getTaskById(int $taskId)
    {
        $task = Tasks::findFirstByTaskId($taskId);

        if (!$task) {
            throw new ServiceException('Task don\'t exists', self::ERROR_TASK_NOT_FOUND);
        }
        return $task;
    }

    public function fillTask(Tasks $tasks, array $data)
    {
        if (!empty(trim($data['name'])))
            $tasks->setName($data['name']);
        if (!empty(trim($data['category_id'])))
            $tasks->setCategoryId($data['category_id']);
        if (!empty(trim($data['description'])))
            $tasks->setDescription($data['description']);
        if (!empty(trim($data['deadline'])))
            $tasks->setDeadline(date('Y-m-d H:i:s', strtotime($data['deadline'])));
        if (!empty(trim($data['price'])))
            $tasks->setPrice($data['price']);
        if (!is_null($data['status']))
            $tasks->setStatus($data['status']);
        if (!empty(trim($data['polygon'])))
            $tasks->setPolygon($data['polygon']);
        if (!empty(trim($data['region_id'])))
            $tasks->setRegionId($data['region_id']);
        if (!empty(trim($data['longitude'])))
            $tasks->setLongitude($data['longitude']);
        if (!empty(trim($data['latitude'])))
            $tasks->setLatitude($data['latitude']);
        if (!empty(trim($data['date_end'])))
            $tasks->setDateEnd(date('Y-m-d H:i:s', strtotime($data['date_end'])));
        if (!empty(trim($data['date_start'])))
            $tasks->setDateStart(date('Y-m-d H:i:s', strtotime($data['date_start'])));
        if (!empty(trim($data['account_id'])))
            $tasks->setAccountId($data['account_id']);
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

    public function deleteTask(Tasks $task)
    {
        if ($task->delete() == false) {
            /*$errors = SupportClass::getArrayWithErrors($task);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete task',
                    self::ERROR_UNABLE_DELETE_TASK, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete task',
                    self::ERROR_UNABLE_DELETE_TASK);
            }*/
            SupportClass::getErrorsWithException($task,self::ERROR_UNABLE_DELETE_TASK,'Unable to delete task');
        }

        return $task;
    }

    public function changeTask(Tasks $task, array $taskData)
    {
        $this->fillTask($task, $taskData);

        if ($task->update() == false) {
            /*$errors = SupportClass::getArrayWithErrors($task);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to change task',
                    self::ERROR_UNABLE_CHANGE_TASK, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to change task',
                    self::ERROR_UNABLE_CHANGE_TASK);
            }*/

            SupportClass::getErrorsWithException($task,self::ERROR_UNABLE_CHANGE_TASK,'Unable to change task');
        }

        return $task;
    }

    public function selectOffer(Offers $offer){
        $this->changeTask($offer->tasks,['status'=>STATUS_WAITING_CONFIRM]);

        $di = DI::getDefault();

        $di->getOfferService()->changeOffer($offer,['selected'=>true]);
    }
}
