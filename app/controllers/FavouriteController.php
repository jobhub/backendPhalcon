<?php

namespace App\Controllers;

use App\Libs\SupportClass;
use App\Models\FavoriteUsers;
use App\Models\Accounts;
use App\Services\FavouriteService;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

use App\Services\AccountService;

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
     * Подписывает текущего пользователя на указанного
     * @method POST
     *
     * @param $type
     *
     * @params account_id = null
     * @params object_id = null
     *
     * @return string - json array Status
     */
    public function setFavouriteAction($type)
    {
        $inputData = $this->request->getJsonRawBody();
        $data['account_id'] = $inputData->account_id;
        $data['object_id'] = $inputData->object_id;

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

            $this->favouriteService->subscribeTo($type,$data['account_id'], $data['object_id']);

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

        return self::successResponse('User was successfully subscribed to other user');
    }

    /**
     * Отменяет подписку на пользователя
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
                $account_id = Accounts::findForUserDefaultAccount($userId);
            }

            if (!Accounts::checkUserHavePermission($userId, $account_id, 'deleteForward')) {
                throw new Http403Exception('Permission error');
            }

            $fav = $this->favouriteService->getSigningTo($type,$account_id,$object_id);

            $this->favouriteService->unsubscribeFrom($fav);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case FavouriteService::ERROR_ACCOUNT_NOT_SUBSCRIBED:
                case FavouriteService::ERROR_UNABLE_UNSUBSCRIBE:
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

        return self::successResponse('Account was successfully unsubscribed from object');
    }

    /**
     * Возвращает подписки текущего пользователя
     * @method GET
     * @return string - json array подписок
     */
    public function getFavouritesAction()
    {
        $userId = self::getUserId();
        return FavoriteUsers::findByUserSubject($userId)->toArray();
    }
}
