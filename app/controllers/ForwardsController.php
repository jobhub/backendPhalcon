<?php

namespace App\Controllers;


use App\Services\AccountService;
use App\Services\LikeService;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Libs\SupportClass;

use App\Models\ForwardsNews;
use App\Models\ForwardsImagesUsers;
use App\Models\Accounts;

use App\Services\ForwardService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http404Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
use PhpParser\Forward;

/**
 * Class ForwardsController
 * Контроллер, который содержит методы для работы с репостами.
 */
class ForwardsController extends AbstractController
{
    /**
     * Создает репост новости
     * @access private
     *
     * @method POST
     *
     * @param $type
     *
     * @params object_id - id новости
     * @params forward_text - текст репоста
     * @params account_id - int id аккаунта, от имени которого добавляется комментарий.
     * Если не указан, то от имени текущего пользователя по умолчанию.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addForwardAction($type)
    {
        $inputData = $this->request->getJsonRawBody();
        $data['object_id'] = $inputData->object_id;
        $data['forward_text'] = $inputData->forward_text;
        $data['account_id'] = $inputData->account_id;

        try {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (empty(trim($data['object_id']))) {
                $errors['object_id'] = 'Missing required parameter \'object_id\'';
            }

            if(!is_null($errors)){
                $errors['errors'] = true;
                $exception = new Http400Exception('Invalid some parameters',self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addForward')) {
                throw new Http403Exception('Permission error');
            }

            $forward = $this->forwardService->createForward($data, $type);

            $forward = $this->forwardService->getForwardByIds($data['account_id'],$forward->getObjectId(),$type);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ForwardService::ERROR_UNABLE_CREATE_FORWARD:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ForwardService::ERROR_INVALID_FORWARD_TYPE:
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

        return self::successResponse('Forward was successfully created', ['forward' => $forward->toArray()]);
    }

    /**
     * Удаляет комментарий указаннного типа
     *
     * @method DELETE
     *
     * @param $type string - тип репоста
     * @param $object_id int id новость
     * @param $account_id int id аккаунта
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteForwardAction($type, $object_id, $account_id = null)
    {
        try {
            $userId = self::getUserId();

            if(is_null($account_id) || !SupportClass::checkInteger($account_id)){
                $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $account_id, 'deleteForward')) {
                throw new Http403Exception('Permission error');
            }

            $forward = $this->forwardService->getForwardByIds($account_id,$object_id, $type);

            $this->forwardService->deleteForward($forward);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ForwardService::ERROR_UNABLE_DELETE_FORWARD:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ForwardService::ERROR_FORWARD_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                case ForwardService::ERROR_INVALID_FORWARD_TYPE:
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

        return self::successResponse('Forward was successfully deleted');
    }

    /**
     * Возвращает комментарии к указанному объекту
     *
     * @method GET
     * @param $type
     * @param $object_id
     * @param $parent_id
     * @param $account_id
     * @param $page
     * @param $page_size
     * @return string - json array массив комментариев
     */
    /*public function getForwardsAction($type, $object_id, $parent_id = null, $account_id = null, $page = 1, $page_size = ForwardsModel::DEFAULT_RESULT_PER_PAGE_PARENT)
    {
        try {
            if (self::isAuthorized()) {
                $userId = self::getUserId();
                if ($account_id == null || !is_integer(intval($account_id))) {
                    $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
                }


                if (!Accounts::checkUserHavePermission($userId, $account_id, 'getForwards')) {
                    throw new Http403Exception('Permission error');
                }

            } else
                $account_id = null;


            if ($parent_id != null && is_integer($parent_id)) {
                $forwards = $this->forwardService->getChildForwards($object_id, $type, $parent_id,$account_id, $page, $page_size);
            } else {
                $forwards = $this->forwardService->getParentForwards($object_id, $type,$account_id, $page, $page_size);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ForwardService::ERROR_INVALID_FORWARD_TYPE:
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
        return $forwards;
    }*/
}