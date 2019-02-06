<?php

namespace App\Controllers;

use App\Models\FavoriteUsers;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

use App\Services\UserInfoService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
/**
 * Контроллер для работы с подписками на пользователей
 * Реализует методы для подписки пользователя на другого пользователя, отписки и получения подписок.
 */
class FavouriteUsersAPIController extends AbstractController
{
    /**
     * Подписывает текущего пользователя на указанного
     * @method POST
     * @params user_id
     * @return string - json array Status
     */
    public function setFavouriteAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['user_id'] = $inputData->user_id;

        try {
            $userId = self::getUserId();

            //проверки
            $errors = null;

            if (empty(trim($data['user_id']))) {
                $errors['user_id'] = 'Missing required parameter "user_id"';
            }

            if ($errors != null) {
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            $this->userInfoService->subscribeToUser($userId, $data['user_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_UNABLE_SUBSCRIBE_USER_TO_USER:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('User was successfully subscribed to other user');
    }

    /**
     * Отменяет подписку на пользователя
     * @method
     * @param $user_id
     * @return string - json array Status
     */
    public function deleteFavouriteAction($user_id)
    {
        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            $errors = null;

            $favUser = $this->userInfoService->getSigningToUser($userId,$user_id);

            $this->userInfoService->unsubscribeFromUser($favUser);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case UserInfoService::ERROR_USER_NOT_SUBSCRIBED_TO_USER:
                case UserInfoService::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_USER:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('User was successfully unsubscribed from user');
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
