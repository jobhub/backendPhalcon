<?php

namespace App\Services;

use App\Models\News;
use App\Models\Requests;

use App\Libs\SupportClass;

/**
 * business logic for requests
 *
 * Class RequestService
 */
class RequestService extends AbstractService
{
    const ADDED_CODE_NUMBER = 16000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_REQUEST = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_REQUEST_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_REQUEST = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_REQUEST = 4 + self::ADDED_CODE_NUMBER;


    public function createRequest(array $requestData)
    {
        $request = new Requests();
        $this->fillRequest($request, $requestData);

        if ($request->create() == false) {
            $errors = SupportClass::getArrayWithErrors($request);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to create request',
                    self::ERROR_UNABLE_CREATE_REQUEST, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to create request',
                    self::ERROR_UNABLE_CREATE_REQUEST);
            }
        }

        return $request;
    }

    public function getRequestById(int $requestId)
    {
        $request = Requests::findFirstByRequestId($requestId);

        if (!$request) {
            throw new ServiceException('Request don\'t exists', self::ERROR_REQUEST_NOT_FOUND);
        }
        return $request;
    }

    public function fillRequest(Requests $request, array $data)
    {
        if (!empty(trim($data['description'])))
            $request->setDescription($data['description']);
        if (!empty(trim($data['service_id'])))
            $request->setServiceId($data['service_id']);
        if (!empty(trim($data['date_end'])))
            $request->setDateEnd(date('Y-m-d H:i:s', strtotime($data['date_end'])));
        if (!empty(trim($data['account_id'])))
            $request->setAccountId($data['account_id']);
        if (!empty(trim($data['status'])))
            $request->setStatus($data['status']);
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

    public function deleteRequest(Requests $request)
    {
        if ($request->delete() == false) {
            $errors = SupportClass::getArrayWithErrors($request);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to delete request',
                    self::ERROR_UNABLE_DELETE_REQUEST, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to delete request',
                    self::ERROR_UNABLE_DELETE_REQUEST);
            }
        }

        return $request;
    }

    public function changeRequest(Requests $request, array $requestData)
    {
        $this->fillRequest($request, $requestData);

        if ($request->update() == false) {
            $errors = SupportClass::getArrayWithErrors($request);
            if (count($errors) > 0)
                throw new ServiceExtendedException('unable to change request',
                    self::ERROR_UNABLE_CHANGE_REQUEST, null, null, $errors);
            else {
                throw new ServiceExtendedException('unable to change request',
                    self::ERROR_UNABLE_CHANGE_REQUEST);
            }
        }

        return $request;
    }
}
