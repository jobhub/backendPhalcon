<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\InvitesCompanyManager;
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
    const ADDED_CODE_NUMBER = 33000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_INVITE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_INVITE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_INVITE_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_INVITE = 4 + self::ADDED_CODE_NUMBER;

    public function createInvite(array $inviteData)
    {
        try {
            $invite = new InvitesCompanyManager();

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

    public function changeInvite(InvitesCompanyManager $invite, array $inviteData)
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

    private function fillInvite(InvitesCompanyManager $invite, array $data)
    {
        if (isset($data['invited']))
            $invite->setInvited($data['invited']);
        if (isset($data['who_invited']))
            $invite->setWhoInvited($data['who_invited']);
        if (isset($data['where_invited']))
            $invite->setWhereInvited($data['where_invited']);
        if (!empty(trim($data['invite_date'])))
            $invite->setInviteDate(date('Y-m-d H:i:sO', strtotime($data['invite_date'])));
    }

    public function getInviteById(int $inviteId)
    {
        try {
            $invite = InvitesCompanyManager::findFirstByInviteId($inviteId);

            if (!$invite || $invite == null) {
                throw new ServiceException('Invite don\'t exists', self::ERROR_INVITE_NOT_FOUND);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $invite;
    }

    public function getInviteByData($invited, $where_invited, $who_invited = null)
    {
        try {
            if($who_invited == null) {
                $invite = InvitesCompanyManager::findFirst([
                    'invited = :invited: and where_invited = :where_invited:',
                    'bind'=> ['invited'=>$invited, 'where_invited'=>$where_invited]
                ]);
            } else{
                $invite = InvitesCompanyManager::findFirst([
                    'invited = :invited: and where_invited = :where_invited: 
                    and who_invited = :who_invited:',
                    'bind'=> [
                        'invited'=>$invited,
                        'where_invited'=>$where_invited,
                        'who_invited'=>$who_invited
                    ]
                ]);
            }

            if (!$invite || $invite == null) {
                throw new ServiceException('Invite don\'t exists', self::ERROR_INVITE_NOT_FOUND);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $invite;
    }

    public function deleteInvite(InvitesCompanyManager $invite)
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
