<?php

namespace App\Controllers;

use App\Libs\SupportClass;
use App\Models\FavouriteModel;
use App\Models\Accounts;
use App\Models\FavouriteProducts;
use App\Models\FavouriteServices;
use App\Models\Products;
use App\Models\Services;
use App\Services\CompanyService;
use App\Services\FavouriteService;
use App\Models\FavoriteCategories;
use App\Services\UserService;
use Phalcon\Http\Client\Exception;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

use App\Services\AccountService;
use App\Services\CategoryService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http404Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Контроллер для работы с подписками на что-либо
 * Реализует методы для подписки, отписки, получения подписок
 */
class FavouriteController extends AbstractController
{
    /**
     * Подписывает текущего пользователя на что-либо
     * @method POST
     *
     * @param $type
     *
     * @params account_id = null
     * @params object_id = null
     *
     * (category)
     * @params radius
     *
     * @return string - json array Status
     */
    public function setFavouriteAction($type)
    {
        $inputData = $this->request->getJsonRawBody();
        $data['account_id'] = $inputData->account_id;
        $data['object_id'] = $inputData->object_id;
        $data['radius'] = $inputData->radius;

        try {
            $userId = self::getUserId();
            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (empty(trim($data['object_id']))) {
                $errors['object_id'] = 'Missing required parameter "object_id"';
            }

            if ($errors != null) {
                $errors['error'] = true;
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addFavourite')) {
                throw new Http403Exception('Permission error');
            }

            $this->favouriteService->subscribeTo($type, $data['account_id'], $data['object_id'], $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case FavouriteService::ERROR_UNABLE_SUBSCRIBE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case FavouriteService::ERROR_INVALID_FAVOURITE_TYPE:
                    $exception = new Http404Exception(
                        _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                        new \Exception('URI not found: ' .
                            $this->request->getMethod() . ' ' . $this->request->getURI())
                    );
                    throw $exception;
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Account was successfully subscribed');
    }

    /**
     * Отменяет подписку
     * @method
     * @param $object_id
     * @param $type
     * @param $account_id = null
     * @return string - json array Status
     */
    public function deleteFavouriteAction($type, $object_id, $account_id = null)
    {
        try {
            $userId = self::getUserId();

            if (is_null($account_id) || !SupportClass::checkInteger($account_id)) {
                $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $account_id, 'deleteForward')) {
                throw new Http403Exception('Permission error');
            }

            $fav = $this->favouriteService->getSigningTo($type, $account_id, $object_id);

            $this->favouriteService->unsubscribeFrom($fav);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case FavouriteService::ERROR_UNABLE_UNSUBSCRIBE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case FavouriteService::ERROR_ACCOUNT_NOT_SUBSCRIBED:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case FavouriteService::ERROR_INVALID_FAVOURITE_TYPE:
                    $exception = new Http404Exception(
                        _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                        new \Exception('URI not found: ' .
                            $this->request->getMethod() . ' ' . $this->request->getURI())
                    );
                    throw $exception;
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Account was successfully unsubscribed');
    }

    /**
     * Возвращает всех подписчиков на указанный аккаунт (а именно, компанию или пользователя)
     *
     * @method GET
     *
     * @param $account_id = null
     * @param $query = null
     * @param $page = 1
     * @param $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE
     *
     * @return string - json array подписок
     */
    public function getSubscribersAction($account_id = null, $query = null,
                                         $page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if ($account_id != null && SupportClass::checkInteger($account_id)) {
            if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                throw new Http403Exception('Permission error');
            }

            $account = Accounts::findFirstById($account_id);
        } else {
            $account = Accounts::findForUserDefaultAccount($userId);
        }

        self::setAccountId($account->getId());

        $result = FavouriteModel::findSubscribers($account, $query, $page, $page_size);

        return self::successPaginationResponse('',$result['data'],$result['pagination']);
    }

    /**
     * Возвращает всех подписчиков указанного пользователя или компании (а именно, компанию или пользователя)
     *
     * @method GET
     *
     * @params $id
     * @params $is_company
     * @params $query = null
     * @params $page = 1
     * @params $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE
     *
     * @return string - json array подписок
     */
    public function getOtherSubscribersAction(/*$id, $is_company = false, $query = null,
                                              $page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE*/)
    {
        $inputData = $this->request->getJsonRawBody();
        $id = $inputData->id;
        $is_company = $inputData->is_company!=null?$inputData->is_company:false;
        $query = $inputData->query;
        $page = $inputData->page;
        $page_size = $inputData->page_size;

        if(is_null($page)||!SupportClass::checkInteger($page))
            $page = 1;
        if(is_null($page_size)||!SupportClass::checkInteger($page_size))
            $page_size = 10;

        if(is_null($id)){
            $errors['id'] = 'Missing required parameter "id"';
        }

        if (!is_null($errors)) {
            $exception = new Http400Exception(_('Invalid some parameters'));
            $errors['errors'] = true;
            throw $exception->addErrorDetails($errors);
        }

        if ($is_company && strtolower($is_company) != "false") {
            $result = FavouriteModel::findSubscribersByCompany($id, $query, $page, $page_size);
        } else {
            $result = FavouriteModel::findSubscribersByUser($id, $query, $page, $page_size);
        }

        return self::successPaginationResponse('',$result['data'],$result['pagination']);
    }

    /**
     * Возвращает все подписки текущего аккаунта (или всех аккаунтов компании, если аккаунт с ней связан)
     *
     * @method GET
     *
     * @param $account_id = null
     * @param $query = null
     * @param $page = 1
     * @param $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE
     *
     * @return string - json array подписок
     */
    public function getSubscriptionsAction($account_id = null, $query = null,
                                           $page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if ($account_id != null && SupportClass::checkInteger($account_id)) {
            if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                throw new Http403Exception('Permission error');
            }

            $account = Accounts::findFirstById($account_id);
        } else {
            $account = Accounts::findForUserDefaultAccount($userId);
        }

        self::setAccountId($account->getId());

        $result = FavouriteModel::findSubscriptions($account, $query, $page, $page_size);
        return self::successPaginationResponse('',$result['data'],$result['pagination']);
    }

    /**
     * Возвращает все подписки указанного пользователя или компании
     *
     * @method POST
     *
     * @params $id = null
     * @params $is_company = null
     * @params $query = null
     * @params $page = 1
     * @params $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE
     *
     * @return string - json array подписок
     */
    public function getOtherSubscriptionsAction(/*$id = null, $is_company = false,$query = null,
                                                $page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE*/)
    {
        try {
            $inputData = $this->request->getJsonRawBody();
            $id = $inputData->id;
            $is_company = $inputData->is_company;
            $query = $inputData->query;
            $page = $inputData->page;
            $page_size = $inputData->page_size;

            $userId = self::getUserId();
            $account = Accounts::findForUserDefaultAccount($userId);
            self::setAccountId($account->getId());

            if ($is_company && strtolower($is_company) != "false") {
                //Here i just check company is exists
                $company = $this->companyService->getCompanyById($id);
                $relatedAccounts = $this->accountService->getForCompanyRelatedAccounts($company->getCompanyId());
            } else {
                //Here i check user on existing
                $user = $this->userService->getUserById($id);
                $account = $this->accountService->getForUserDefaultAccount($user->getUserId());
                $relatedAccounts = $account->getRelatedAccounts();
            }

            try {
                $result = FavouriteModel::findSubscriptions($relatedAccounts, $query, $page, $page_size);
            }catch (\Exception $e){
                echo $e;
            }
            return self::successPaginationResponse('', $result['data'], $result['pagination']);

        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Возвращает избранные услуги пользователя
     *
     * @method GET
     *
     * @param $account_id = null
     * @param $page = 1
     * @param $page_size = Services::DEFAULT_RESULT_PER_PAGE
     *
     * @return string - json array с подписками (просто id-шники)
     */
    public function getFavouriteServicesAction($account_id = null, $page = 1, $page_size = Services::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if ($account_id != null && SupportClass::checkInteger($account_id)) {
            if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                throw new Http403Exception('Permission error');
            }
        } else {
            $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
        }

        self::setAccountId($account_id);

        $result = FavouriteServices::findFavourites($account_id, $page, $page_size);

        return self::successPaginationResponse('',$result['data'],$result['pagination']);
    }

    /**
     * Возвращает избранные товары пользователя
     *
     * @method GET
     *
     * @param $account_id = null
     * @param $page = 1
     * @param $page_size = Products::DEFAULT_RESULT_PER_PAGE
     *
     * @return string - json array с подписками (просто id-шники)
     */
    public function getFavouriteProductsAction($account_id = null, $page = 1, $page_size = Products::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if ($account_id != null && SupportClass::checkInteger($account_id)) {
            if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                throw new Http403Exception('Permission error');
            }
        } else {
            $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
        }

        self::setAccountId($account_id);

        $result = FavouriteProducts::findFavourites($account_id, $page, $page_size);

        return self::successPaginationResponse('',$result['data'],$result['pagination']);
    }

    /**
     * Меняет радиус на получение уведомлений для подписки на категорию
     *
     * @method PUT
     *
     * @params radius
     * @params category_id
     * @params account_id
     * @return string - json array Status
     */
    public function editRadiusInFavouriteCategoryAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['radius'] = $inputData->radius;
        $data['category_id'] = $inputData->category_id;
        $data['account_id'] = $inputData->account_id;

        $userId = self::getUserId();

        if ($data['account_id'] != null && SupportClass::checkInteger($data['account_id'])) {
            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'getNews')) {
                throw new Http403Exception('Permission error');
            }
        } else {
            $data['account_id'] = Accounts::findForUserDefaultAccount($userId)->getId();
        }
        try {
            $this->categoryService->editRadius($data['account_id'], $data['category_id'], $data['radius']);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_UNABlE_CHANGE_RADIUS:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_DO_NOT_SIGNED:
                    throw new Http500Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Radius successfully changed');
    }

    /**
     * Возвращает все подписки пользователя на категории
     *
     * @access private
     *
     * @GET
     *
     * @param $account_id
     * @param $page
     * @param $page_size
     *
     * @return string - json array - подписки пользователя
     */
    public function getFavouritesCategoriesAction($account_id = null, $page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if ($account_id != null && SupportClass::checkInteger($account_id)) {
            if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                throw new Http403Exception('Permission error');
            }
        } else {
            $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
        }

        self::setAccountId($account_id);

        $result = FavoriteCategories::findForUser($account_id, $page, $page_size);

        return self::successPaginationResponse('',$result['data'],$result['pagination']);
    }

    /**
     * Возвращает все подписки пользователя указанного типа (услуги, категории, товары).
     *
     * @access private
     *
     * @GET
     *
     * @param $type
     * @param $account_id
     * @param $page
     * @param $page_size
     *
     * @return string - json array - подписки пользователя
     */
    /*public function getFavouritesAction($type, $account_id = null, $page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if ($account_id != null && SupportClass::checkInteger($account_id)) {
            if (!Accounts::checkUserHavePermission($userId, $account_id, 'getNews')) {
                throw new Http403Exception('Permission error');
            }
        } else {
            $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
        }

        self::setAccountId($account_id);

        switch ($type){
            case 'category':{
                $result = FavoriteCategories::findForUser($account_id, $page, $page_size);
                break;
            }
            case 'service':{
                $result = FavouriteServices::findFavourites($account_id, $page, $page_size);
                break;
            }
            case 'product':{
                $result = FavouriteProducts::findFavourites($account_id, $page, $page_size);
                break;
            }
        }

        return self::successPaginationResponse('',$result['data'],$result['pagination']);
    }*/
}
