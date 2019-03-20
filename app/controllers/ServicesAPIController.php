<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http403Exception;
use App\Models\Accounts;
use App\Models\FavouriteServices;
use App\Models\TradePoints;
use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\CommonService;
use App\Services\PhoneService;
use App\Services\PointService;
use App\Services\ServiceService;
use Phalcon\Http\Client\Exception;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

use App\Libs\SupportClass;

use App\Models\Services;

use App\Services\ImageService;
use App\Services\NewsService;
use App\Services\TagService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class ServicesAPIController
 * Контроллер для работы с услугами.
 * Содержит методы для поиска услуг, CRUD для услуг.
 * Методы для связывания/отвязывания услуг и точек оказания услуг.
 */
class ServicesAPIController extends AbstractController
{
    /**
     * Возвращает все услуги заданной компании
     *
     * @access public
     * @method GET
     *
     * @param $id
     * @param $is_company
     * @param $page
     * @param $page_size
     * @param $account_id
     * @return string -  массив услуг в виде:
     *      [{serviceid, description, datepublication, pricemin, pricemax,
     *      regionid, name, rating, [Categories], [images (массив строк)] {TradePoint}, [Tags],
     *      {Userinfo или Company} }].
     */
    public function getServicesForSubjectAction($id, $is_company = false, $account_id = null, $page = 1, $page_size = Services::DEFAULT_RESULT_PER_PAGE)
    {
        if(self::isAuthorized()) {
            $userId = self::getUserId();

            $account = $this->accountService->checkPermissionOrGetDefaultAccount($userId, $account_id);

            self::setAccountId($account->getId());
        }

        if ($is_company && strtolower($is_company) != "false" && strtolower($is_company)!='null') {
            $relatedAccounts = Accounts::getRelatedAccountsForCompany($is_company);
            //$services = Services::findServicesByCompanyId($id,$page,$page_size);
        } else {
            $account = Accounts::findForUserDefaultAccount($id);
            $relatedAccounts = $account->getRelatedAccounts();
            //$services = Services::findServicesByUserId($id,$page,$page_size);
        }
        $services = Services::findServicesByAccount($relatedAccounts,$page,$page_size);
        return self::successPaginationResponse('',$services['data'],$services['pagination']);
    }

    /**
     * Возвращает все услуги данного юзера (или его компании).
     *
     * @method GET
     *
     * @param $account_id - если не указан, то будут возвращены услуги текущего пользователя.
     *        Иначе компании, в которой он должен быть хотя бы менеджером.
     * @param $page
     * @param $page_size
     * @return string -  массив услуг в виде:
     *      [{serviceid, description, datepublication, pricemin, pricemax,
     *      regionid, name, rating, [Categories], [images (массив строк)] {TradePoint}, [Tags],
     *      {Userinfo или Company} }].
     */
    public function getOwnServicesAction($account_id = null, $page = 1, $page_size = Services::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();
        if ($account_id == null || !SupportClass::checkInteger($account_id)) {
            $accountId = Accounts::findForUserDefaultAccount($userId)->getId();
            $this->session->set('accountId',$accountId);
            $services = Services::findServicesByUserId($userId,$page,$page_size);
        } else {
            if(!Accounts::checkUserHavePermission($userId,$account_id,'getServices')){
                throw new Http403Exception('Permission error');
            }

            $account = $this->accountService->getAccountById($account_id);

            $this->session->set('accountId',$account_id);
            $services = Services::findServicesByCompanyId($account->getCompanyId(),$page,$page_size);
        }

        return self::successPaginationResponse('',$services['data'],$services['pagination']);
    }

    /**
     * Возвращает услуги. Во-первых, принимает тип запроса в параметре type_query:
     * 0 - принимает строку user_query, центральную точку для поиска - center => [longitude => ..., latitude =>  ...],
     * крайнюю точку для определения радиуса - diagonal => [longitude => ..., latitude =>  ...],
     * массив регионов (id-шников) (regions_id). возвращает
     * список услуг и всего им соответствующего;
     * 1 - запрос на получение элементов интеллектуального поиска. Принимает те же данные, что и в 0-вом запросе.
     * Возвращает массив с типом элемента (строкой - 'company', 'service' и 'category'), id элемента и его названием для отображения в строке
     *  ([{type : ..., id : ..., name : ...}, {...}]);
     * 2 - еще один запрос на получение услуг. Принимает id элемента и тип строкой (type), как отдавалось в запрос 1.
     * Возвращает массив услуг, как в 0-вом запросе.
     * 3 - запрос на получение услуг по категориям. Принимает массив категорий categoriesId, центральную и крайнюю точку
     * и массив регионов, как в 0-вом запросе. Возвращает массив услуг, как везде.
     * 4 - запрос для поиска по области. Центральная точка, крайняя точка, массив регионов, которые попадут в область.
     * Возвращает массив услуг, как везде.
     * 5 - запрос для поиска с фильтрами. Принимает центральную, диагональные точки, массив категорий,
     * минимальную и максимальную цены (price_min, price_max) и минимальный рейтинг (rating_min)
     *
     * @access public
     *
     * @method POST
     *
     * @params int type_query (обязательно)
     * @params array center (необязательно) [longitude, latiitude]
     * @params array diagonal (необязательно) [longitude, latiitude]
     * @params string type (необязательно) 'company', 'service', 'category'.
     * @params int id (необязательно)
     * @params string user_query (необязательно)
     * @params array regions_id (необязательно) массив регионов,
     * @params array categories_id (необязательно) массив категорий,
     * @params price_min
     * @params price_max
     * @params rating_min
     *
     * @return string json массив [status, service, company/user_info,[categories],[trade_points],[images]] или
     *   json массив [status, [{type : ..., id : ..., name : ...}, {...}]].
     */
    public function getServicesAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        if ($data['type_query'] == 0) {
            if (strlen($data['user_query']) < 3) {
                $exception = new Http400Exception('Invalid some parameters');
                throw $exception->addErrorDetails(['user_query' => 'user query must contain at least 3 characters']);
            }

            $result['services'] = Services::getServicesByQuery($data['user_query'], $data['center'],
                $data['diagonal'], $data['regionsId']);

        } elseif ($data['type_query'] == 1) {
            $results['autocomplete'] = Services::getAutocompleteByQuery($data['user_query'],
                $data['center'], $data['diagonal'], $data['regions_id']);

        } elseif ($data['type_query'] == 2) {

            if ($data['type'] == 'category') {
                $categoriesId = $data['id'];

                if (is_array($categoriesId)) {
                    $allCategories = [];
                    foreach ($categoriesId as $categoryId) {
                        $allCategories[] = $categoryId;
                        $childCategories = Categories::findByParentid($categoryId);
                        foreach ($childCategories as $childCategory) {
                            $allCategories[] = $childCategory->getCategoryId();
                        }
                    }
                } else {
                    $allCategories[] = $categoriesId;
                    $childCategories = Categories::findByParentid($categoriesId);
                    foreach ($childCategories as $childCategory) {
                        $allCategories[] = $childCategory->getCategoryId();
                    }
                }

                $result['services'] = Services::getServicesByElement($data['type'],
                    $allCategories,
                    $data['center'], $data['diagonal'], $data['regions_id']);

            } else {
                $result['services'] = Services::getServicesByElement($data['type'], array($data['id']),
                    $data['center'], $data['diagonal'], $data['regions_id']);
            }

        } elseif ($data['type_query'] == 3) {
            $categoriesId = $data['categories_id'];

            if (is_array($categoriesId)) {
                $allCategories = [];
                foreach ($categoriesId as $categoryId) {
                    $allCategories[] = $categoryId;
                    $childCategories = Categories::findByParentid($categoryId);
                    foreach ($childCategories as $childCategory) {
                        $allCategories[] = $childCategory->getCategoryId();
                    }
                }
            } else {
                $allCategories[] = $categoriesId;
                $childCategories = Categories::findByParentid($categoriesId);
                foreach ($childCategories as $childCategory) {
                    $allCategories[] = $childCategory->getCategoryId();
                }
            }

            $result['services'] = Services::getServicesByElement('category',
                $allCategories,
                $data['center'], $data['diagonal'], $data['regions_id']);

        } elseif ($data['type_query'] == 4) {
            $result['services'] = Services::getServicesByQuery($data['user_query'],
                $data['center'], $data['diagonal'], $data['regions_id']);

        } elseif ($data['type_query'] == 5) {

            $categoriesId = $data['categories_id'];

            if (is_array($categoriesId)) {
                $allCategories = [];
                foreach ($categoriesId as $categoryId) {
                    $allCategories[] = $categoryId;
                    $childCategories = Categories::findByParentid($categoryId);
                    foreach ($childCategories as $childCategory) {
                        $allCategories[] = $childCategory->getCategoryId();
                    }
                }
            } else {
                $allCategories[] = $categoriesId;
                $childCategories = Categories::findByParentid($categoriesId);
                foreach ($childCategories as $childCategory) {
                    $allCategories[] = $childCategory->getCategoryId();
                }
            }

            $result['services'] = Services::getServicesWithFilters($data['user_query'],
                $data['center'], $data['diagonal'], $data['regions_id'], $categoriesId, $data['price_min'],
                $data['price_max'], $data['rating_min']);

        } elseif ($data['type_query'] == 6) {
            if (strlen($data['user_query']) < 3) {
                $exception = new Http400Exception('Invalid some parameters');
                throw $exception->addErrorDetails(['user_query' => 'user query must contain at least 3 characters']);
            }

            $result['services'] = Services::getServicesByQueryByTags($data['user_query'],
                $data['center'], $data['diagonal'], $data['regions_id']);
        } elseif($data['type_query'] == 7) {

            $result['services'] = Services::getServicesInClustersByQueryByTags($data['user_query'],
                $data['low_left'], $data['high_right']);
        }
        else {
            $exception = new Http400Exception('Invalid some parameters');
            throw $exception->addErrorDetails(['type_query' => 'user query must contain at least 3 characters']);
        }

        return self::successResponse('', $result);
    }

    /**
     * Удаляет указанную услугу
     * @access private
     *
     * @method DELETE
     *
     * @param $service_id
     * @return Response - с json массивом в формате Status
     */
    public function deleteServiceAction($service_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = $this->serviceService->getServiceById($service_id);

            if (!Accounts::checkUserHavePermission($userId, $service->getAccountId(), 'deleteNews')) {
                throw new Http403Exception('Permission error');
            }

            $this->serviceService->deleteService($service);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_UNABLE_DELETE_SERVICE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Service was successfully deleted');
    }

    /**
     * Редактирует указанную услугу
     * @access private
     *
     * @method PUT
     *
     * @params service_id
     * @params description
     * @params name
     * @params price_min, price_max (или же вместо них просто price)
     * @params region_id
     * @params deleted_tags - массив int-ов - id удаленных тегов
     * @params added_tags - массив строк
     * @return Response - с json массивом в формате Status
     */
    public function editServiceAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['service_id'] = $inputData->service_id;
        $data['description'] = $inputData->description;
        $data['name'] = $inputData->name;
        $data['price_min'] = $inputData->price_min;
        $data['price_max'] = $inputData->price_max;
        $data['price'] = $inputData->price;
        $data['region_id'] = $inputData->region_id;
        $data['deleted_tags'] = $inputData->deleted_tags;
        $data['added_tags'] = $inputData->added_tags;

        try {
            //validation
            if (empty(trim($data['service_id']))) {
                $errors['service_id'] = 'Missing required parameter "service_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = $this->serviceService->getServiceById($data['service_id']);

            if (!Accounts::checkUserHavePermission($userId, $service->getAccountId(), 'editService')) {
                throw new Http403Exception('Permission error');
            }

            $this->db->begin();


            if (!empty(trim($data['price']))) {
                $data['price_min'] = $data['price'];
                $data['price_max'] = $data['price'];
            }

            $service = $this->serviceService->changeService($service, $data);

            if($data['deleted_tags']!=null)
            foreach ($data['deleted_tags'] as $tagId) {
                $serviceTag = $this->tagService->getTagForService($tagId, $service->getServiceId());
                $this->tagService->deleteTagFromService($serviceTag);
            }

            if($data['added_tags']!=null)
            foreach ($data['added_tags'] as $tag) {
                $this->tagService->addTagToService($tag, $service->getServiceId());
            }

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ServiceService::ERROR_UNABLE_CHANGE_SERVICE:
                case TagService::ERROR_UNABLE_CREATE_TAG:
                case TagService::ERROR_UNABLE_DELETE_TAG:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                case TagService::ERROR_TAG_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Service was successfully changed');
    }

    /**
     * Добавляет новую услугу к субъекту. Если не указана компания, можно добавить категории.
     * Добавлять услуги можно только компании.
     *
     * Услуга привязывается к той точке (считается, что она единственная для компании),
     * которая была создана при создании бизнес-аккаунта.
     *
     * @method POST
     *
     * //@params (необязательные) массив old_points - массив id tradePoint-ов,
     * //@params (необязательные) массив new_points - массив объектов TradePoints
     *
     * @params (необязательные) account_id, description, name, price_min, price_max (или же вместо них просто price)
     *           (необязательно) region_id,
     *           (необязательно) categories array of int - массив id категорий.
     * @params массив строк tags с тегами.
     * @params прикрепленные изображения. Именование роли не играет.
     *
     * @return string - json array. Если все успешно - [status, service_id], иначе [status, errors => <массив ошибок>].
     */
    public function addServiceAction()
    {
        //$inputData = $this->request->getJsonRawBody();
        $inputData = json_decode(json_encode($this->request->getPost()));
        $data['account_id'] = $inputData->account_id;
        $data['description'] = $inputData->description;
        $data['name'] = $inputData->name;
        $data['price_min'] = $inputData->price_min;
        $data['price_max'] = $inputData->price_max;
        $data['price'] = $inputData->price;
        $data['region_id'] = $inputData->region_id;
        $data['tags'] = $inputData->tags;
        $data['old_points'] = $inputData->old_points;
        $data['new_points'] = $inputData->new_points;
        $data['categories'] = $inputData->categories;
        $this->db->begin();
        try {
            //validation
            /*if(empty(trim($data['service_id']))) {
                $errors['service_id'] = 'Missing required parameter "service_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }*/

            $userId = self::getUserId();

            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addService')) {
                throw new Http403Exception('Permission error');
            }

            if (!empty(trim($data['price']))) {
                $data['price_min'] = $data['price'];
                $data['price_max'] = $data['price'];
            }

            $data['date_publication'] = date('Y-m-d H:i:s');

            $service = $this->serviceService->createService($data);

            if($data['tags']!=null)
            foreach ($data['tags'] as $tag) {
                $this->tagService->addTagToObject($tag, $service->getServiceId(),TagService::TYPE_SERVICE);
            }

            /*if($data['old_points']!=null)
            foreach ($data['old_points'] as $old_point_id) {
                $point = $this->pointService->getPointById($old_point_id);
                $this->pointService->addPointToService($point->getPointId(), $service->getServiceId());
            }

            if($data['new_points']!=null)
            foreach ($data['new_points'] as $new_point_info) {
                $clipped_point_info = $this->pointService->clipDataForCreation($new_point_info);
                $point = $this->pointService->createPoint($clipped_point_info);
                $this->pointService->addPointToService($point->getPointId(), $service->getServiceId());

                foreach ($new_point_info['new_phones'] as $phone) {
                    $this->phoneService->addPhoneToPoint($phone, $point->getPointId());
                }
            }*/

            //$account = $this->accountService->getAccountById($data['account_id']);

            $pointByDefault = TradePoints::findFirstByAccountId($data['account_id']);
            if($pointByDefault)
                $this->pointService->addPointToService($pointByDefault->getPointId(), $service->getServiceId());

            $account = $this->accountService->getAccountById($data['account_id']);

            if ($account->getCompanyId() == null) {
                if($data['categories']!=null)
                foreach ($data['categories'] as $categoryId) {
                    $this->categoryService->linkUserWithCategory($categoryId, $account->getUserId());
                }
            } else {
                if($data['categories']!=null)
                foreach ($data['categories'] as $categoryId) {
                    $this->categoryService->linkCompanyWithCategory($categoryId, $account->getCompanyId(),false);
                }
            }

            if (count($this->request->getUploadedFiles()) > 0) {
                $ids = $this->imageService->createImagesToService($this->request->getUploadedFiles(), $service);
                $this->imageService->saveImagesToService($this->request->getUploadedFiles(), $service, $ids);
            }

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ServiceService::ERROR_UNABLE_CREATE_SERVICE:
                case TagService::ERROR_UNABLE_CREATE_TAG:
                case PointService::ERROR_UNABLE_CREATE_POINT:
                case PointService::ERROR_UNABLE_ADD_POINT_TO_SERVICE:
                case PhoneService::ERROR_UNABLE_CREATE_PHONE:
                case PhoneService::ERROR_UNABLE_ADD_PHONE_TO_POINT:
                case CategoryService::ERROR_UNABlE_LINK_CATEGORY_WITH_COMPANY:
                case CategoryService::ERROR_UNABlE_LINK_CATEGORY_WITH_USER:
                case ImageService::ERROR_UNABLE_CHANGE_IMAGE:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                case TagService::ERROR_TAG_NOT_FOUND:
                case PointService::ERROR_POINT_NOT_FOUND:
                case CategoryService::ERROR_CATEGORY_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Service was successfully created',['service_id'=>$service->getServiceId()]);
    }

    /**
     * Добавляет картинки к услуге
     *
     * @method POST
     *
     * @params (обязательно) service_id
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addImagesAction()
    {
        try {
            /*$sender = $this->request->getJsonRawBody();

            $data['news_id'] = $sender->news_id;*/

            $data['service_id'] = $this->request->getPost('service_id');

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = $this->serviceService->getServiceById($data['service_id']);

            if (!Accounts::checkUserHavePermission($userId, $service->getAccountId(), 'editService')) {
                throw new Http403Exception('Permission error');
            }

            $this->db->begin();

            $ids = $this->imageService->createImagesToService($this->request->getUploadedFiles(), $service);

            $this->imageService->saveImagesToService($this->request->getUploadedFiles(), $service, $ids);

            $this->db->commit();
        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ImageService::ERROR_UNABLE_CHANGE_IMAGE:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Image was successfully added to service');
    }

    /**
     * Удаляет картинку из списка картинок услуги
     *
     * @method DELETE
     *
     * @param $image_id integer id изображения
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageAction($image_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $image = $this->imageService->getImageById($image_id, ImageService::TYPE_SERVICE);

            if (!Accounts::checkUserHavePermission($userId, $image->Services->getAccountId(), 'editService')) {
                throw new Http403Exception('Permission error');
            }

            $this->imageService->deleteImage($image);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ImageService::ERROR_UNABLE_DELETE_IMAGE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ImageService::ERROR_IMAGE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Image was successfully deleted');
    }

    /**
     * Связывает услугу с точкой оказания услуг
     *
     * @method POST
     *
     * @params (обязательные) service_id, point_id
     *
     * @return string - json array в формате Status - результат операции
     */
    public function linkServiceWithPointAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['service_id'] = $inputData->service_id;
        $data['point_id'] = $inputData->point_id;

        try {

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = $this->serviceService->getServiceById($data['service_id']);

            $point = $this->pointService->getPointById($data['point_id']);

            if (!Accounts::checkUserHavePermission($userId, $point->getAccountId(), 'changeService')
                || !Accounts::checkUserHavePermission($userId, $service->AccountId(), 'changeService')) {
                throw new Http403Exception('Permission error');
            }

            $this->pointService->addPointToService($point->getPointId(), $service->getServiceId());

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case PointService::ERROR_UNABLE_ADD_POINT_TO_SERVICE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                case PointService::ERROR_POINT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('All ok');
    }

    /**
     * Убирает связь услуги и точки оказания услуг
     *
     * @method DELETE
     *
     * @param $service_id
     * @param $point_id
     *
     * @return string - json array в формате Status - результат операции
     */
    public function unlinkServiceAndPointAction($service_id, $point_id)
    {
        $inputData = $this->request->getJsonRawBody();
        $data['service_id'] = $service_id;
        $data['point_id'] = $point_id;

        try {

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = $this->serviceService->getServiceById($data['service_id']);

            $point = $this->pointService->getPointById($data['point_id']);

            if (!Accounts::checkUserHavePermission($userId, $point->getAccountId(), 'changeService')
                || !Accounts::checkUserHavePermission($userId, $service->AccountId(), 'changeService')) {
                throw new Http403Exception('Permission error');
            }

            $this->pointService->deletePointFromService($point->getPointId(), $service->getServiceId());

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case PointService::ERROR_UNABLE_DELETE_POINT_FROM_SERVICE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                case PointService::ERROR_POINT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('All ok');
    }

    /**
     * Подтверждает выполнить заявку на оказание услуги
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return Response - с json массивом в формате Status
     */
    public function confirmRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_WAITING_CONFIRM) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя подтвердить заказ на данном этапе']
                    ]
                );
                return $response;
            }

            $request->setStatus(STATUS_EXECUTING);

            if (!$request->save()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Предоставляющий услугу субъект утверждает, что выполнил заявку
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return Response - с json массивом в формате Status
     */
    public function performRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_EXECUTING) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя завершить заказ на данном этапе']
                    ]
                );
                return $response;
            }

            $request->setStatus(STATUS_EXECUTED_EXECUTOR);

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Заказчик отменяет заявку.
     *
     * @method PUT
     *
     * @param requestId
     *
     * @return string - json array в формате Status
     */
    public function rejectRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeletedWithCascade::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_WAITING_CONFIRM &&
                $request->getStatus() != STATUS_EXECUTING &&
                $request->getStatus() != STATUS_EXECUTED_EXECUTOR &&
                $request->getStatus() != STATUS_EXECUTED_CLIENT) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя отказаться от заказа на данном этапе']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() == STATUS_WAITING_CONFIRM)
                $request->setStatus(STATUS_NOT_CONFIRMED);
            else {
                $request->setStatus(STATUS_NOT_EXECUTED);
            }

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет картинку к услуге.
     * @param $serviceId
     * @return Response с json массивом типа Status
     */
    /*public function addImagesHandler($serviceId)
    {
        $response = new Response();
        include(APP_PATH . '/library/SimpleImage.php');
        // Проверяем установлен ли массив файлов и массив с переданными данными
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $filenames = [];

            $images = ImagesServices::findByServiceid($serviceId);
            $countImages = count($images);

            $this->db->begin();

            foreach($files as $file) {


                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $format = $imageFormat;
                if ($imageFormat == 'jpeg' || 'jpg')
                    $imageFormat = IMAGETYPE_JPEG;
                elseif ($imageFormat == 'png')
                    $imageFormat = IMAGETYPE_PNG;
                elseif ($imageFormat == 'gif')
                    $imageFormat = IMAGETYPE_GIF;
                else {
                    $this->db->rollback();
                    $response->setJsonContent(
                        [
                            "error" => ['Данный формат не поддерживается'],
                            "status" => STATUS_WRONG
                        ]
                    );
                    return $response;
                }

                $filename = BASE_PATH . '/img/services/' . hash('crc32', $service->getServiceId()) . '_'
                    . $countImages . '.' . $format;

                $imageFullName = str_replace(BASE_PATH, '', $filename);

                $newimage = new ImagesServices();
                $newimage->setServiceId($serviceId);
                $newimage->setImagePath($imageFullName);

                if (!$newimage->save()) {
                    $errors = [];
                    $this->db->rollback();
                    foreach ($newimage->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }
                $filenames[] = ['name' => $filename, 'format' => $imageFormat, 'tempname' => $file->getTempName()];
                $countImages+=1;
            }

            foreach($filenames as $filename){
                $image = new SimpleImage();
                $image->load($filename['tempname']);
                $image->resizeToWidth(200);
                $image->save($filename['name'], $filename['format']);
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        }
        $response->setJsonContent(
            [
                "status" => STATUS_OK
            ]
        );
        return $response;
    }*/

    /**
     * Добавляет все отправленные файлы изображений к услуге. Общее количество
     * фотографий для одной услуги на данный момент не более 10.
     *
     * @param $serviceId
     * @return Response с json массивом типа Status
     */
    /*public function addImagesHandler($serviceId)
    {
        include(APP_PATH . '/library/SimpleImage.php');
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $filenames = [];

            $images = ImagesServices::findByServiceid($serviceId);
            $countImages = count($images);

            if (($countImages + count($files)) > ImagesServices::MAX_IMAGES) {
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для услуги. 
                        Можно сохранить для одной услуги не более чем ' . ImagesServices::MAX_IMAGES . ' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $this->db->begin();
            $imagesIds = [];

            foreach ($files as $file) {

                $newimage = new ImagesServices();
                $newimage->setServiceId($serviceId);
                $newimage->setImagePath("magic_string");

                if (!$newimage->save()) {
                    $errors = [];
                    $this->db->rollback();
                    foreach ($newimage->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }

                $imagesIds[] = $newimage->getImageId();

                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $filename = ImageLoader::formFullImageName('services', $imageFormat, $serviceId, $newimage->getImageId());

                $newimage->setImagePath($filename);

                if (!$newimage->update()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadService($file->getTempName(), $file->getName(), $serviceId, $imagesIds[$i]);
                $i++;
                if ($result != ImageLoader::RESULT_ALL_OK || $result === null) {
                    if ($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED) {
                        $error = 'Формат одного из изображений не поддерживается';
                    } elseif ($result == ImageLoader::RESULT_ERROR_NOT_SAVED) {
                        $error = 'Не удалось сохранить изображение';
                    } else {
                        $error = 'Ошибка при загрузке изображения';
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => [$error]
                        ]
                    );
                    return $response;
                }
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        }
        $response->setJsonContent(
            [
                "status" => STATUS_OK
            ]
        );
        return $response;
    }*/

    /**
     * Увеличивает на 1 счетчик числа просмотров услуги.
     * @method PUT
     * @params service_id
     * @return string - json array в формате Status
     */
    public function incrementNumberOfDisplayForServiceAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['service_id'] = $inputData->service_id;

        try {
            //validation
            if (empty(trim($data['service_id']))) {
                $errors['service_id'] = 'Missing required parameter "service_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = $this->serviceService->getServiceById($data['service_id']);

            if (!Accounts::checkUserHavePermission($userId, $service->getAccountId(), 'editService')) {
                throw new Http403Exception('Permission error');
            }

            $this->serviceService->changeService($service,
                ['number_of_display' => $service->getNumberOfDisplay() + 1]);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_UNABLE_CHANGE_SERVICE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Service was successfully changed');
    }

    /**
     * Возвращает все заказы, которые могут быть связаны с данной услугой.
     * На самом деле нет, конечно же. Логики того, как это будет делаться нет.
     *
     * @method GET
     *
     * @param $service_id
     * @return string - json array tasks
     */
    public function getTasksForService($service_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = $this->serviceService->getServiceById($service_id);

            if (!Accounts::checkUserHavePermission($userId, $service->getAccountId(), 'getTasksForService')) {
                throw new Http403Exception('Permission error');
            }

            return Services::getTasksForService($service_id);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Возвращает публичную информацию об услуге.
     * @access public.
     *
     * @method GET
     *
     * @param $service_id
     * @param $account_id
     *
     * @return string - json array {status, service, [points => {point, [phones]}], reviews (до двух)}
     */
    public function getServiceInfoAction($service_id, $account_id = null)
    {
        try {
            $service = Services::findServiceById($service_id, Services::publicColumns);

            if(self::isAuthorized()) {
                $userId = self::getUserId();

                $account = $this->accountService->checkPermissionOrGetDefaultAccount($userId, $account_id);

                self::setAccountId($account->getId());

                $handledService = Services::handleServiceFromArray([$service->toArray()])[0];
                $account = $this->accountService->getAccountById($handledService['account_id']);

                $query = Services::getQueryForFindServicesByAccount($account->getRelatedAccounts());

                $sortCondition = $this->commonService->getSortCondition(CommonService::TYPE_SERVICE, $query, $handledService);

                try {
                    $count = SupportClass::getCountForObjectByQuery($sortCondition['from'],
                        $sortCondition['conditions'], $sortCondition['bind']);

                }catch (\Exception $e){
                    echo $e;
                }
                return $this->successPaginationResponse('', $handledService, ['pagination' => ['position' => $count + 1]]);
            }
        }catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_SERVICE_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Подписывает текущего пользователя или его аккаунт (компанию) на услугу
     *
     * @method POST
     *
     * @params service_id
     * @params account_id = null
     *
     * @return Response с json ответом в формате Status
     */
    /*public function setFavouriteAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['service_id'] = $inputData->service_id;
        $data['account_id'] = $inputData->account_id;

        try {
            $userId = self::getUserId();

            if(is_null($data['account_id']) || !is_integer($data['account_id'])){
                $data['account_id'] = Accounts::findForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'setFavouriteService')) {
                throw new Http403Exception('Permission error');
            }

            if (empty(trim($data['service_id']))) {
                $errors['service_id'] = 'Missing required parameter "service_id"';
            }

            if ($errors != null) {
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            $this->serviceService->subscribeToService($data['account_id'], $data['service_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_UNABLE_SUBSCRIBE_USER_TO_SERVICE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Account was successfully subscribed to service');
    }*/


    /**
     * Отменяет подписку на услугу
     *
     * @method DELETE
     *
     * @param $service_id
     * @param $account_id = null
     *
     * @return Response с json ответом в формате Status
     */
    /*public function deleteFavouriteAction($service_id, $account_id = null)
    {
        try {
            $userId = self::getUserId();

            if(is_null($account_id) || !is_integer($account_id)){
                $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $account_id, 'deleteFavouriteService')) {
                throw new Http403Exception('Permission error');
            }

            $fafServ = $this->serviceService->getSigningToService($account_id,$service_id);

            $this->serviceService->unsubscribeFromService($fafServ);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_SERVICE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ServiceService::ERROR_USER_NOT_SUBSCRIBED_TO_SERVICE:
                    throw new Http422Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Account was successfully unsubscribed from service');
    }*/

    /*public
    function addImagesToAllServicesAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();

            $services = Services::find();

            foreach ($services as $service) {
                $randnumber = rand(0, 3);

                if ($randnumber > 0) {
                    $imageserv = new ImagesServices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/desert.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                    $randnumber--;
                }

                if ($randnumber > 0) {
                    $imageserv = new ImagesServices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/butterfly.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                    $randnumber--;
                }
                if ($randnumber > 0) {
                    $imageserv = new ImagesServices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/flower.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                }
            }

            $response->setJsonContent([
                'status' => STATUS_OK,
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }*/
}