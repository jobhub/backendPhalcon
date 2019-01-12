<?php

namespace App\Controllers;

use App\Services\UserInfoService;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

use App\Models\FavoriteCompanies;
use App\Models\TradePoints;
use App\Models\Accounts;
use App\Services\CompanyService;
use App\Services\AccountService;
use App\Services\ImageService;
use App\Services\PhoneService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Контроллер для работы с подписками на компании
 * Реализует методы для подписки пользователя на компании, отписки и получения подписок.
 */
class FavouriteCompaniesAPIController extends AbstractController
{
    /**
     * Подписывает текущего пользователя на компанию
     *
     * @method POST
     *
     * @params company_id
     *
     * @return Response с json ответом в формате Status
     */
    public function setFavouriteAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['company_id'] = $inputData->company_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $errors = null;

            if (empty(trim($data['company_id']))) {
                $errors['company_id'] = 'Missing required parameter "company_id"';
            }

            if ($errors != null) {
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            $this->userInfoService->subscribeToCompany($userId, $data['company_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_UNABLE_SUBSCRIBE_USER_TO_COMPANY:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('User was successfully subscribed to company');
    }


    /**
     * Отменяет подписку на компанию
     *
     * @method DELETE
     *
     * @param $company_id
     *
     * @return Response с json ответом в формате Status
     */
    public function deleteFavouriteAction($company_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $errors = null;

            $favComp = $this->userInfoService->getSigningToCompany($userId,$company_id);

            $this->userInfoService->unsubscribeFromCompany($favComp);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_USER_NOT_SUBSCRIBE_TO_COMPANY:
                case UserInfoService::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_COMPANY:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('User was successfully unsubscribed from company');
    }


    /**
     * Возвращает подписки пользователя на компании
     *
     * @return string - json array с подписками (просто id-шники)
     */
    public function getFavouritesAction()
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        return FavoriteCompanies::findByUserId($userId)->toArray();
    }
}
