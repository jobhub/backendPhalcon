<?php

namespace App\Controllers;

use App\Libs\SupportClass;
use App\Models\FavouriteModel;
use App\Models\Accounts;
use App\Models\FavouriteServices;
use App\Models\Services;
use App\Services\FavouriteService;
use App\Models\FavoriteCategories;
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

            $this->favouriteService->subscribeTo($type,$data['account_id'], $data['object_id'],$data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case FavouriteService::ERROR_UNABLE_SUBSCRIBE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }catch (ServiceException $e) {
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

        return self::successResponse('User was successfully subscribed');
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

            if(is_null($account_id) || !SupportClass::checkInteger($account_id)){
                $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $account_id, 'deleteForward')) {
                throw new Http403Exception('Permission error');
            }

            $fav = $this->favouriteService->getSigningTo($type,$account_id,$object_id);

            $this->favouriteService->unsubscribeFrom($fav);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case FavouriteService::ERROR_UNABLE_UNSUBSCRIBE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }catch (ServiceException $e) {
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
    public function getSubscribersAction($account_id = null,$query = null,
                                         $page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if($account_id!=null && SupportClass::checkInteger($account_id)){
            if(!Accounts::checkUserHavePermission($userId,$account_id,'getNews')){
                throw new Http403Exception('Permission error');
            }

            $account = Accounts::findFirstById($account_id);
        } else{
            $account = Accounts::findForUserDefaultAccount($userId);
        }

        self::setAccountId($account->getId());

        return FavouriteModel::findSubscribers($account,$query,$page,$page_size);
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
    public function getSubscriptionsAction($account_id = null,$query = null,
                                         $page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if($account_id!=null && SupportClass::checkInteger($account_id)){
            if(!Accounts::checkUserHavePermission($userId,$account_id,'getNews')){
                throw new Http403Exception('Permission error');
            }

            $account = Accounts::findFirstById($account_id);
        } else{
            $account = Accounts::findForUserDefaultAccount($userId);
        }

        self::setAccountId($account->getId());

        return FavouriteModel::findSubscriptions($account,$query,$page,$page_size);
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

        if($account_id!=null && SupportClass::checkInteger($account_id)){
            if(!Accounts::checkUserHavePermission($userId,$account_id,'getNews')){
                throw new Http403Exception('Permission error');
            }
        } else{
            $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
        }

        self::setAccountId($account_id);

        return FavouriteServices::findFavourites($account_id,$page,$page_size);
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

        if($data['account_id']!=null && SupportClass::checkInteger($data['account_id'])){
            if(!Accounts::checkUserHavePermission($userId,$data['account_id'],'getNews')){
                throw new Http403Exception('Permission error');
            }
        } else{
            $data['account_id'] = Accounts::findForUserDefaultAccount($userId)->getId();
        }
        try{
            $this->categoryService->editRadius($data['account_id'],$data['category_id'],$data['radius']);
        }catch(ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_UNABlE_CHANGE_RADIUS:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_DON_NOT_SIGNED:
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
    public function getFavouritesCategoriesAction($account_id = null,$page = 1, $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if($account_id!=null && SupportClass::checkInteger($account_id)){
            if(!Accounts::checkUserHavePermission($userId,$account_id,'getNews')){
                throw new Http403Exception('Permission error');
            }
        } else{
            $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
        }

        self::setAccountId($account_id);

        return FavoriteCategories::findForUser($account_id,$page,$page_size);
    }
}
