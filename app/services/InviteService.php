<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\InvitesCompanyManager;
use App\Models\InvitesModel;
use App\Models\InvitesRegisterToBeManager;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class InviteService extends AbstractService
{
    const TYPE_INVITE_TO_BE_MANAGER = 1;
    const TYPE_INVITE_TO_REGISTER_AND_BE_MANAGER = 2;

    const ADDED_CODE_NUMBER = 33000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_INVITE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_INVITE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_INVITE_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_INVITE = 4 + self::ADDED_CODE_NUMBER;


    const ERROR_INVALID_INVITE_TYPE = 5 + self::ADDED_CODE_NUMBER;

    public function createNewObjectByType($type)
    {
        switch ($type) {
            case self::TYPE_INVITE_TO_BE_MANAGER:
                $invite = new InvitesCompanyManager();
                break;
            case self::TYPE_INVITE_TO_REGISTER_AND_BE_MANAGER:
                $invite = new InvitesRegisterToBeManager();
                break;
            default:
                throw new ServiceException('Invalid type of invite', self::ERROR_INVALID_INVITE_TYPE);
        }
        return $invite;
    }

    public function getModelByType($type)
    {
        switch ($type) {
            case self::TYPE_INVITE_TO_BE_MANAGER:
                $invite = 'App\\Models\\InvitesCompanyManager';
                break;
            case self::TYPE_INVITE_TO_REGISTER_AND_BE_MANAGER:
                $invite = 'App\\Models\\InvitesRegisterToBeManager';
                break;
            default:
                throw new ServiceException('Invalid type of invite', self::ERROR_INVALID_INVITE_TYPE);
        }
        return $invite;
    }

    public function createInvite(array $inviteData, $type)
    {
        try {
            $invite = $this->createNewObjectByType($type);

            $this->fillInvite($invite, $inviteData);

            if ($invite->save() == false) {
                $errors = SupportClass::getArrayWithErrors($invite);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable create invite',
                        self::ERROR_UNABLE_CREATE_INVITE, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable create invite',
                        self::ERROR_UNABLE_CREATE_INVITE);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $invite;
    }

    public function changeInvite($invite, array $inviteData)
    {
        try {
            $this->fillInvite($invite, $inviteData);
            if ($invite->update() == false) {
                $errors = SupportClass::getArrayWithErrors($invite);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable change invite',
                        self::ERROR_UNABLE_CHANGE_INVITE, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable change invite',
                        self::ERROR_UNABLE_CHANGE_INVITE);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $invite;
    }

    private function fillInvite($invite, array $data)
    {
        if (isset($data['who_invited']))
            $invite->setWhoInvited($data['who_invited']);
        if (isset($data['where_invited']))
            $invite->setWhereInvited($data['where_invited']);
        if (!empty(trim($data['invite_date'])))
            $invite->setInviteDate(date('Y-m-d H:i:sO', strtotime($data['invite_date'])));

        if (isset($data['invited']))
            $invite->setInvited($data['invited']);
    }

    public function getInviteById(int $inviteId, $type = self::TYPE_INVITE_TO_BE_MANAGER)
    {
        try {
            $model = $this->getModelByType($type);

            $invite = $model::findFirstByInviteId($inviteId);

            if (!$invite || $invite == null) {
                throw new ServiceException('Invite don\'t exists', self::ERROR_INVITE_NOT_FOUND);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $invite;
    }

    public function getInviteByData($invited, $where_invited,
                                    $type = self::TYPE_INVITE_TO_BE_MANAGER, $who_invited = null)
    {
        try {

            /*$model = $this->getModelByType($type);

            if ($who_invited == null) {
                $invite = $model::findFirst([
                    'invited = :invited: and where_invited = :where_invited:',
                    'bind' => ['invited' => $invited, 'where_invited' => $where_invited]
                ]);
            } else {
                $invite = $model::findFirst([
                    'invited = :invited: and where_invited = :where_invited: 
                    and who_invited = :who_invited:',
                    'bind' => [
                        'invited' => $invited,
                        'where_invited' => $where_invited,
                        'who_invited' => $who_invited
                    ]
                ]);
            }*/

            $invite = InvitesModel::findInviteByData($invited,$where_invited,$type,$who_invited);

            if (!$invite || $invite == null) {
                throw new ServiceException('Invite don\'t exists', self::ERROR_INVITE_NOT_FOUND);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $invite;
    }

    public function deleteInvite(InvitesModel $invite)
    {
        try {
            if (!$invite->delete()) {
                $errors = SupportClass::getArrayWithErrors($invite);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to delete invite',
                        self::ERROR_UNABLE_DELETE_INVITE, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to delete invite',
                        self::ERROR_UNABLE_DELETE_INVITE);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
