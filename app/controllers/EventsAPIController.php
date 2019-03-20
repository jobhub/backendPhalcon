<?php

namespace App\Controllers;

use App\Models\Events;
use App\Models\Products;
use App\Models\Tasks;
use App\Services\CommonService;
use App\Services\CompanyService;
use App\Services\EventService;
use App\Services\OfferService;
use App\Services\PhoneService;
use App\Services\ProductService;
use App\Services\TagService;
use App\Services\TaskService;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

use App\Models\ImagesUsers;
use App\Models\News;
use App\Models\Accounts;

use App\Services\ImageService;
use App\Services\NewsService;
use App\Services\AccountService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;


/**
 * Class EventsAPIController
 * Предназначен для работы с акциями.
 * Реализует CRUD для акций и поиск.
 */
class EventsAPIController extends AbstractController
{
    /**
     * Adding event
     *
     * @access private
     * @method POST
     * @input Form data
     *
     * @params name string
     * @params description string
     * @params account_id int
     * @params center [longitude, latitude]
     * @params radius
     * @params tags array of string
     * @params images in $_FILES
     *
     * @return string - json array  формате Status
     */
    public function addEventAction()
    {
        $inputData = json_decode(json_encode($this->request->getPost()), true);
        $data['name'] = $inputData['name'];
        $data['description'] = $inputData['description'];
        $data['account_id'] = $inputData['account_id'];
        $data['center'] = $inputData['center'];
        $data['radius'] = $inputData['radius'];
        $data['account_id'] = $inputData['account_id'];
        $data['service_id'] = $inputData['service_id'];
        $data['tags'] = $inputData['tags'];
        $data['images'] = $this->request->getUploadedFiles();

        $this->db->begin();
        try {
            $userId = self::getUserId();

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addEvent')) {
                throw new Http403Exception('Permission error');
            }

            $account = $this->accountService->getAccountById($data['account_id']);

            if ($account->getCompanyId() == null) {
                $errors['account_id'] = 'Only companies can add events';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception('Some parameters are invalid');
                throw $exception->addErrorDetails($errors);
            }

            $data['longitude'] = $data['center']['longitude'];
            $data['latitude'] = $data['center']['latitude'];

            $event = $this->eventService->createEvent($data);
            $event = $this->eventService->getEventById($event->getEventId());

            $handledEvent = Events::handleEventFromArray($event->toArray());

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case EventService::ERROR_UNABLE_CREATE_EVENT:
                case ImageService::ERROR_UNABLE_CHANGE_IMAGE:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                case TagService::ERROR_UNABLE_CREATE_TAG:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Event was successfully created',
            ['event' => $handledEvent]);
    }

    /**
     * Deleting of the event
     *
     * @access private
     * @method DELETE
     *
     * @param $event_id
     *
     * @return string - json array в формате Status
     *
     */
    public function deleteEventAction($event_id)
    {
        try {
            $userId = self::getUserId();

            $event = $this->eventService->getEventById($event_id);

            if (!Accounts::checkUserHavePermission($userId, $event->getAccountId(), 'deleteProduct')) {
                throw new Http403Exception('Permission error');
            }

            $this->eventService->deleteEvent($event);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case EventService::ERROR_UNABLE_DELETE_EVENT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case EventService::ERROR_EVENT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Event was successfully deleted');
    }

    /**
     * Editing of the product.
     *
     * @access private
     * @method PUT
     * @params event_id int
     * @params name string
     * @params description string
     * @params center [longitude, latitude]
     * @params radius
     * @return string - json array в формате Status`
     */
    public function editEventAction()
    {
        $inputData = json_decode($this->request->getRawBody(), true);
        $data['event_id'] = $inputData['event_id'];
        $data['name'] = $inputData['name'];
        $data['description'] = $inputData['description'];
        $data['center'] = $inputData['center'];
        $data['radius'] = $inputData['radius'];

        try {
            //validation
            if (empty(trim($data['event_id']))) {
                $errors['event_id'] = 'Missing required parameter "event_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $userId = self::getUserId();

            $event = $this->eventService->getEventById($data['event_id']);

            if (!Accounts::checkUserHavePermission($userId, $event->getAccountId(), 'editEvent')) {
                throw new Http403Exception('Permission error');
            }

            unset($data['product_id']);
            $data['longitude'] = $data['center']['longitude'];
            $data['latitude'] = $data['center']['latitude'];

            $event = $this->eventService->changeEvent($event, $data);

            $handledEvent = Events::handleEventFromArray($event->toArray());

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case EventService::ERROR_UNABLE_CHANGE_EVENT:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case EventService::ERROR_EVENT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Event was successfully changed', ['event' => $handledEvent]);
    }

    /**
     * Возвращает публичную информацию об акции.
     * @access public.
     *
     * @method GET
     *
     * @param $event_id
     * @params $account_id
     *
     * @return array
     */
    public function getEventInfoAction($event_id)
    {
        try {
            $inputData = $this->request->getQuery();
            $account_id = $inputData['account_id'];
            $event = $this->eventService->getEventById($event_id);

            if (self::isAuthorized()) {
                $userId = self::getUserId();

                $account = $this->accountService->checkPermissionOrGetDefaultAccount($userId, $account_id);

                self::setAccountId($account->getId());
            }

            return self::successResponse('', Events::handleEventFromArray($event->toArray()));

        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case EventService::ERROR_EVENT_NOT_FOUND:
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Возвращает акции указанной компании.
     * active and divided_by_active can use only owner of the company.
     *
     * @access public
     * @method GET
     *
     * @params account_id int
     * @params company_id int
     * @params active boolean
     * @params divided_by_active boolean
     * @params page_size int
     * @params page int
     *
     * @return string - json array с новостями (или их отсутствием)
     */
    public function getEventsAction()
    {
        $userId = self::getUserId();
        $inputData = $this->request->getQuery();
        $data['account_id'] = $inputData['account_id'];
        $data['company_id'] = $inputData['company_id'];
        $data['page'] = $inputData['page'];
        $data['page_size'] = $inputData['page_size'];

        $data['active'] = $inputData['active'];
        $data['divided_by_active'] = $inputData['divided_by_active'];

        if (!is_null($data['active']))
            $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);

        if (!is_null($data['divided_by_active']))
            $data['divided_by_active'] = filter_var($data['divided_by_active'], FILTER_VALIDATE_BOOLEAN);


        try {

            //validation
            if (empty(trim($data['company_id']))) {
                $errors['company_id'] = 'Missing required parameter "company_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            if (self::isAuthorized()) {
                $account = $this->accountService->checkPermissionOrGetDefaultAccount($userId, $data['account_id']);

                self::setAccountId($account->getId());
            }

            $company = $this->companyService->getCompanyById($data['company_id']);
            $accounts = Accounts::getRelatedAccountsForCompany($company->getCompanyId());

            if (Accounts::checkUserHavePermissionToCompany($userId, $company->getCompanyId(), 'getEvents')) {
                $events = Events::findEventsByAccount($accounts, $data['active'], $data['divided_by_active'],
                    $data['page'],
                    $data['page_size']);
            } else {
                $events = Events::findEventsByAccount($accounts, true, null,
                    $data['page'],
                    $data['page_size']);
            }

        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return self::successPaginationResponse('', $events['data'], $events['pagination']);
    }

    /**
     * Возвращает акции, полученные по поисковой строке для указанной геопозиции.
     * active and divided_by_active can use only owner of the company.
     *
     * @access public
     * @method POST
     *
     * @params query string
     * @params page_size int
     * @params page int
     * @params center [longitude, latitude]
     *
     * @return string - json array с новостями (или их отсутствием)
     */
    public function findEventsAction()
    {
        $inputData = json_decode($this->request->getRawBody(), true);

        $data['query'] = $inputData['query'];
        $data['center'] = $inputData['center'];

        $data['page'] = filter_var($data['page'], FILTER_VALIDATE_INT);
        $data['page'] = (!$data['page']) ? 1 : $data['page'];

        $data['page_size'] = filter_var($data['page_size'], FILTER_VALIDATE_INT);
        $data['page_size'] = (!$data['page_size']) ? Products::DEFAULT_RESULT_PER_PAGE : $data['page_size'];

        if (!empty($data['center']['longitude']) && !empty($data['center']['latitude'])) {
            $data['center']['longitude'] = filter_var($data['center']['longitude'], FILTER_VALIDATE_FLOAT);
            $data['center']['latitude'] = filter_var($data['center']['latitude'], FILTER_VALIDATE_FLOAT);

            if (empty($data['center']['longitude']) || empty($data['center']['latitude']))
                $data['center'] = null;
        } else {
            $data['center'] = null;
        }
        try {

            if (self::isAuthorized()) {
                $userId = self::getUserId();

                $account = $this->accountService->checkPermissionOrGetDefaultAccount($userId, $data['account_id']);

                self::setAccountId($account->getId());
            }

            $events = Events::findEventsWithFilters($data['query'], ['center'=>$data['center']],
                $data['page'], $data['page_size']);

        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return self::successPaginationResponse('', $events['data'], $events['pagination']);
    }

    /**
     * Увеличивается статистику акции
     *
     * @access private
     * @method POST
     *
     * @params event_id int
     * @params number_of_clicks
     * @params average_display_time
     * @params number_of_display
     *
     * @return string - json array с новостями (или их отсутствием)
     */
    public function changeStatisticsAction(){
        $inputData = json_decode($this->request->getRawBody(), true);
        $data['event_id'] = $inputData['event_id'];
        $data['number_of_clicks'] = $inputData['number_of_clicks'];
        $data['average_display_time'] = $inputData['average_display_time'];
        $data['number_of_display'] = $inputData['number_of_display'];

        try {
            //validation
            if (empty(trim($data['event_id']))) {
                $errors['event_id'] = 'Missing required parameter "event_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            /*if(!self::isAuthorized())
                throw new Http403Exception('You must be authorized');*/


            $event = $this->eventService->getEventById($data['event_id']);

            $this->eventService->incrementStatistics($event->statistics, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case EventService::ERROR_UNABLE_CHANGE_EVENT:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case EventService::ERROR_EVENT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Statistics of the event was successfully changed');
    }
}