<?php

namespace App\Services;

use App\Models\ForwardsImagesUsers;
use App\Models\ForwardsInNewsModel;
use App\Models\ForwardsNews;
use App\Models\ForwardsServices;
use App\Models\CompanyRole;
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

    const ADDED_CODE_NUMBER = 20000;

    /** Unable to create user */
    const ERROR_FORWARD_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_FORWARD = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_FORWARD = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_FORWARD = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_INVALID_FORWARD_TYPE = 5 + self::ADDED_CODE_NUMBER;

    public function createForward(array $forwardData, $type)
    {
        switch ($type) {
            case self::TYPE_NEWS:
                $forward = new ForwardsNews();
                break;
            default:
                throw new ServiceException('Invalid type of forward', self::ERROR_INVALID_FORWARD_TYPE);
        }

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

    private function fillForward(ForwardsInNewsModel $forward, array $data, $type)
    {
        if (!empty(trim($data['account_id'])))
            $forward->setAccountId($data['account_id']);
        if (!empty(trim($data['object_id'])))
            $forward->setObjectId($data['object_id']);
        if (!empty(trim($data['forward_text'])))
            $forward->setForwardText($data['forward_text']);
        if (!empty(trim($data['forward_date'])))
            $forward->setForwardDate(date('Y-m-d H:i:sO', strtotime($data['forward_date'])));
    }

    public function getForwardByIds($accountId, $objectId, $type)
    {
        switch ($type) {
            case self::TYPE_NEWS:
                $forward = ForwardsNews::findForwardByIds($accountId, $objectId);
                break;
            default:
                throw new ServiceException('Invalid type of forward', self::ERROR_INVALID_FORWARD_TYPE);
        }

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
