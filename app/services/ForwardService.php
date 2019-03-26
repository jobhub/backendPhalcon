<?php

namespace App\Services;

use App\Models\ForwardsImagesUsers;
use App\Models\ForwardsInNewsModel;
use App\Models\ForwardsNews;
use App\Models\ForwardsProducts;
use App\Models\ForwardsServices;
use App\Models\CompanyRole;
use App\Models\News;
use Phalcon\DI\FactoryDefault as DI;

use App\Models\Companies;

use App\Libs\SupportClass;
use App\Libs\ImageLoader;
//models
use App\Models\Userinfo;
use App\Models\Settings;
use App\Controllers\HttpExceptions\Http500Exception;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ForwardService extends AbstractService
{
    const TYPE_NEWS = 'news';
    const TYPE_SERVICE = 'service';
    const TYPE_IMAGE_USER = 'image-user';
    const TYPE_PRODUCT = 'product';

    const ADDED_CODE_NUMBER = 20000;

    /** Unable to create user */
    const ERROR_FORWARD_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_FORWARD = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_FORWARD = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_FORWARD = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_FORWARD_TYPE = 5 + self::ADDED_CODE_NUMBER;

    public function getModelByType($type)
    {
        switch ($type) {
            case self::TYPE_NEWS:
                $model = 'ForwardsNews';
                break;
            case self::TYPE_SERVICE:
                $model = 'ForwardsServices';
                break;
            case self::TYPE_IMAGE_USER:
                $model = 'ForwardsImagesUsers';
                break;
            case self::TYPE_PRODUCT:
                $model = 'ForwardsProducts';
                break;
            default:
                throw new ServiceException('Invalid type of forward', self::ERROR_INVALID_FORWARD_TYPE);
        }
        return 'App\Models\\' . $model;
    }

    public function createNewObjectByType($type)
    {
        switch ($type) {
            case self::TYPE_NEWS:
                $forward = new ForwardsNews();
                break;
            case self::TYPE_SERVICE:
                $forward = new ForwardsServices();
                break;
            case self::TYPE_IMAGE_USER:
                $forward = new ForwardsImagesUsers();
                break;
            case self::TYPE_PRODUCT:
                $forward = new ForwardsProducts();
                break;
            default:
                throw new ServiceException('Invalid type of forward', self::ERROR_INVALID_FORWARD_TYPE);
        }
        return $forward;
    }

    public function createForward(array $forwardData, $type)
    {
        //$forward = $this->createNewObjectByType($type);
        $forward = new News();

        $newsData = [];
        $newsData['news_text'] = $forwardData['forward_text'];
        $newsData['publish_date'] = $forwardData['forward_date'];

        $this->fillForward($forward, $forwardData, $type);

        if ($forward->create() == false) {
            $errors = SupportClass::getArrayWithErrors($forward);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable create forward',
                    self::ERROR_UNABLE_CREATE_FORWARD, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable create forward',
                    self::ERROR_UNABLE_CREATE_FORWARD);
            }
        }

        return $forward;
    }

    public function changeForward(ForwardsInNewsModel $forward, array $forwardData, $type)
    {
        $this->fillForward($forward, $forwardData, $type);
        if ($forward->update() == false) {
            $errors = SupportClass::getArrayWithErrors($forward);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable change forward',
                    self::ERROR_UNABLE_CHANGE_FORWARD, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable change forward',
                    self::ERROR_UNABLE_CHANGE_FORWARD);
            }
        }
        return $forward;
    }

    private function fillForward(News $forward, array $data, $type)
    {
        if (!empty(trim($data['account_id'])))
            $forward->setAccountId($data['account_id']);
        /*if (!empty(trim($data['object_id'])))
            $forward->setObjectId($data['object_id']);
        if (!empty(trim($data['forward_text'])))
            $forward->setForwardText($data['forward_text']);
        if (!empty(trim($data['forward_date'])))
            $forward->setForwardDate(date('Y-m-d H:i:sO', strtotime($data['forward_date'])));*/
    }

    public function getForwardByIds($accountId, $objectId, $type)
    {
        $model = $this->getModelByType($type);


        $forward = $model::findForwardByIds($accountId, $objectId);

        if (!$forward) {
            throw new ServiceException('Forward don\'t exists', self::ERROR_FORWARD_NOT_FOUND);
        }
        return $forward;
    }

    public function deleteForward(ForwardsInNewsModel $forward)
    {
        try {
            if (!$forward->delete()) {
                $errors = SupportClass::getArrayWithErrors($forward);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to delete forward',
                        self::ERROR_UNABLE_DELETE_FORWARD, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to delete forward',
                        self::ERROR_UNABLE_DELETE_FORWARD);
                }
            }
        } catch (\PDOException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
    }
}
