<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 22.01.2019
 * Time: 14:07
 */

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http403Exception;
use App\Libs\SupportClass;
use App\Models\ImagesModel;
use App\Models\Accounts;

use App\Services\ImageService;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http404Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class ImageController
 * Контроллер для изображений. Реализует общий CRUD для всех изображений (без редактирования).
 */
class ImageController extends AbstractController
{
    /**
     * Возвращает изображения для указанного объекта.
     * Тип может быть:
     *      user
     *      news
     *      review
     *      service
     *      company (пока еще не реализовано)
     *
     * @access private
     *
     * @method GET
     * @param $type string
     * @param $object_id int
     * @param $page
     * @param $page_size
     * @param $account_id
     * @params (обязательно) изображения. Именование не важно.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function getImagesAction($type, $object_id,$account_id = null, $page = 1,
                                    $page_size = ImagesModel::DEFAULT_RESULT_PER_PAGE)
    {
        $userId = self::getUserId();

        if($account_id!=null && is_integer(intval($account_id))){
            if(!Accounts::checkUserHavePermission($userId,$account_id,'getNews')){
                throw new Http403Exception('Permission error');
            }
        } else{
            $account_id = Accounts::findForUserDefaultAccount($userId)->getId();
        }

        self::setAccountId($account_id);

        try {
            $images = $this->imageService->getImages($object_id,$type,$page,$page_size);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
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

        return $images;
    }
}