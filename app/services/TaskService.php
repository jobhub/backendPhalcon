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

        $di = DI::getDefault();
        $db = $di->getDb();
        $db->begin();

        $this->fillTask($task, $taskData);

        if ($task->create() == false) {
            $db->rollback();
            SupportClass::getErrorsWithException($task,self::ERROR_UNABLE_CREATE_TASK,'Unable to create task');
        }

        if(isset($taskData['images']) && is_array($taskData['images'])) {
            $ids = $this->imageService->createImagesToObject($taskData['images'], $task, ImageService::TYPE_TASK);
            $this->imageService->saveImagesToObject($taskData['images'], $task, $ids, ImageService::TYPE_TASK);
        }

        $db->commit();
        return $task;
    }

    public function getTaskById(int $taskId)
    {
        $task = Tasks::findById($taskId);

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

        if (isset($data['description']))
            $tasks->setDescription($data['description']);

        if (!empty(trim($data['deadline'])))
            $tasks->setDeadline(date('Y-m-d H:i:s', strtotime($data['deadline'])));

        if (isset($data['price']))
            $tasks->setPrice($data['price']);

        if (isset($data['status']))
            $tasks->setStatus($data['status']);

        if(SupportClass::checkDouble($data['longitude']) && SupportClass::checkDouble($data['latitude'])){
            $marker = $this->markerService->createMarkerWithCity($data['longitude'],$data['latitude']);
            $tasks->setMarkerId($marker->getMarkerId());
        }

        if (!empty(trim($data['date_start'])))
            $tasks->setDateStart(date('Y-m-d H:i:s', strtotime($data['date_start'])));

        if (!empty(trim($data['account_id'])))
            $tasks->setAccountId($data['account_id']);
    }

    public function deleteTask(Tasks $task)
    {
        if ($task->delete() == false) {
            SupportClass::getErrorsWithException($task,self::ERROR_UNABLE_DELETE_TASK,'Unable to delete task');
        }

        return $task;
    }

    public function changeTask(Tasks $task, array $taskData)
    {
        $di = DI::getDefault();
        $db = $di->getDb();

        $db->begin();

        $this->fillTask($task, $taskData);

        if ($task->update() == false) {
            $db->rollback();
            SupportClass::getErrorsWithException($task,self::ERROR_UNABLE_CHANGE_TASK,'Unable to change task');
        }

        /*if(isset($taskData['deleted_images']) && is_array($taskData['deleted_images'])) {
            foreach ($taskData['deleted_images'] as $image_id) {
                $image = $this->imageService->getImageById($image_id);
                $this->imageService->deleteImage($image);
            }
        }

        if(isset($taskData['added_images']) && is_array($taskData['added_images'])) {
            $ids = $this->imageService->createImagesToObject($taskData['added_images'], $task, ImageService::TYPE_TASK);
            $this->imageService->saveImagesToObject($taskData['added_images'], $task, $ids, ImageService::TYPE_TASK);
        }*/

        $db->commit();
        return $task;
    }

    public function selectOffer(Offers $offer){
        $this->changeTask($offer->tasks,['status'=>STATUS_WAITING_CONFIRM]);

        $di = DI::getDefault();

        $di->getOfferService()->changeOffer($offer,['selected'=>true]);
    }
}
