<?php

namespace App\Services;


use App\Controllers\AbstractHttpException;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\Groups;
use App\Models\UserChatGroups;

use App\Models\Users;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class GroupService extends AbstractService
{
    const ADDED_CODE_NUMBER = 24000;

    const ERROR_TRANSACTION = 1 + self::ADDED_CODE_NUMBER;

    const ERROR_UNABLE_TO_ACCESS_GROUP = 5 + self::ADDED_CODE_NUMBER;

    public function create($user_id, $data)
    {
        if (is_null($data["name"])) {
            throw new Http400Exception(_('Missing group name'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        if (strlen(trim($data["name"])) == 0) {
            throw new Http400Exception(_('Wrong data'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        try {
            $manager = new TxManager();
            $transaction = $manager->get();

            $chatHis = $this->chatHistoryService->createChatHistory($transaction);

            $group = new Groups();
            $group->setTransaction($transaction);

            $group->setCreatorId($user_id);
            $group->setName($data['name']);
            $group->setChatHistId($chatHis->getId());

            if ($group->save() === false) {
                $transaction->rollback(
                    'Cannot save Group'
                );
            }

            $user_line = new UserChatGroups();
            $user_line->setTransaction($transaction);

            $user_line->setIsAdmin(true);
            $user_line->setGroupId($group->getId());
            $user_line->setUserId($user_id);

            if ($user_line->save() === false) {
                $transaction->rollback(
                    'Cannot save UserChatGroups'
                );
            }

            $transaction->commit();

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return $group;
    }


    public function addUsers($data)
    {
        $user_ids = $data["users"];
        try {
            if (!isset($user_ids) || !is_array($user_ids)) {
                throw new Http400Exception(_('Missing users'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }

            if (!isset($data["group_id"]) || !is_integer($data["group_id"])) {
                throw new Http400Exception(_('Missing group id'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }

            $group = Groups::findFirst($data["group_id"]);
            if (!$group)
                throw new Http400Exception(_('Unable to access to the group'), AbstractHttpException::BAD_REQUEST_CONTENT);

            foreach ($user_ids as $user_id) {
                $exist_user = Users::isUserExist($user_id);
                if ($exist_user) {
                    $is_already_on_group = $this->isUserOnGroup($user_id, $group->getId());
                    if (!$is_already_on_group) {
                        $user_line = new UserChatGroups();
                        $user_line->setGroupId($group->getId());
                        $user_line->setUserId($user_id);
                        $user_line->save();
                    }
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return true;
    }

    /**
     * get Users of a channel
     *
     * @param $data
     * @return array
     */
    public function getUsers($data)
    {
        if (!isset($data["group_id"]) || !is_integer($data["group_id"])) {
            throw new Http400Exception(_('Missing group id'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        $group = Groups::findFirst($data["group_id"]);
        if (!$group)
            throw new Http400Exception(_('Unable to access to the group'), AbstractHttpException::BAD_REQUEST_CONTENT);

        $toRet = [];
        //$toRet['creator'] = $group->getRelated('User')->getUserShortInfo();
        $toRet['users'] = [];
        $toRet['admins'] = [];
        $subscribes = $group->getRelated('UserChatGroups', [
            'order' => 'create_at ASC',
        ]);
        foreach ($subscribes as $subscribe) {
            $user = $subscribe->getRelated('Users');
            $userInfo = $user->getUserShortInfo();
            if ($subscribe->isAdmin()) {
                array_push($toRet['admins'], $userInfo);
            } else {
                array_push($toRet['users'], $userInfo);
            }
        }
        return $toRet;
    }

    /**
     * delete many users to group
     *
     * @param $data
     * @return bool
     */
    public function removeUsers($data)
    {
        $user_ids = $data['users'];
        try {

            if (!isset($user_ids) || !is_array($user_ids)) {
                throw new Http400Exception(_('Missing users'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            if (!isset($data["group_id"]) || !is_integer($data["group_id"])) {
                throw new Http400Exception(_('Missing group id'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $group_id = $data["group_id"];
            $user_id = $data["user_id"];
            $group = Groups::findFirst($group_id);
            if (!$group)
                throw new Http400Exception(_('Unable to access to the group'), AbstractHttpException::BAD_REQUEST_CONTENT);



            $subscribe = $this->getUserGroupLine($user_id, $group_id);
            if (is_null($subscribe) || !$subscribe->isAdmin())
                // if user is not admin of the group
                throw new Http400Exception(_('Unable to access to this channel : user must be the creator of the channel'), self::ERROR_UNABLE_TO_ACCESS_GROUP);

            foreach ($user_ids as $id) {
                if($id != $user_id){
                    $subscribe = self::getUserGroupLine($id,$group_id);
                    if (!is_null($subscribe)) {
                        $subscribe->delete();
                    }
                }
            }

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Leave a group
     *
     * @param $data
     * @return bool
     */
    public function leaveGroup($data)
    {
        try {

            if (!isset($data["group_id"]) || !is_integer($data["group_id"])) {
                throw new Http400Exception(_('Missing group id'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $group_id = $data["group_id"];
            $user_id = $data["user_id"];
            $group = Groups::findFirst($group_id);
            if (!$group)
                throw new Http400Exception(_('Unable to access to the group'), AbstractHttpException::BAD_REQUEST_CONTENT);



            $subscribe = $this->getUserGroupLine($user_id, $group_id);
            $subscribe->delete();

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * add many users to admin or remove to public of private channels
     *
     * @param $data
     * @return bool
     */
    public function manageAdmins($data)
    {
        $is_admin = true;
        if(!is_null($data['type']) && is_bool($data['type']))
            $is_admin = $data['type'];
        $group_id = $data['group_id'];
        $user_id = $data['user_id'];
        $user_ids = $data['users'];
        try {
            if (!isset($user_ids) || !is_array($user_ids)) {
                throw new Http400Exception(_('Missing users'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            if (!isset($data["group_id"]) || !is_integer($data["group_id"])) {
                throw new Http400Exception(_('Missing group id'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }

            $subscribe = $this->getUserGroupLine($user_id, $group_id);
            if (is_null($subscribe) || !$subscribe->isAdmin())
                // if user is not admin of the group
                throw new Http400Exception(_('Unable to access to this channel : user must be the creator of the channel'), self::ERROR_UNABLE_TO_ACCESS_GROUP);


            foreach ($user_ids as $id) {
                $subscribe = $this->getUserGroupLine($id, $group_id);
                if (!is_null($subscribe)) {
                    $subscribe->setIsAdmin($is_admin);
                    $subscribe->update();
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Get channels of user
     *
     * @param $data array
     * @return array
     */
    public function getGroups($data)
    {
        $user_id = $data['user_id'];
        $subscribes = UserChatGroups::findEnabled([
            'conditions' => 'user_id = :user_id:',
            'bind' => [
                "user_id" => $user_id
            ]
        ]);
        $toRet = [];
        foreach ($subscribes as $subscribe) {
            $channel = $subscribe->getRelated('Groups', [
                'columns' => Groups::PUBLIC_COLUMNS
            ]);
            if($channel)
            {
                $item=['infos' => $channel];
                $item['is_admin'] = $subscribe->isAdmin();
                array_push($toRet, $item);
            }

        }
        return $toRet;
    }

    /**
     * Get channels of user
     *
     * @param $data array
     * @return array
     */
    public function getFromSpam($data)
    {
        $user_id = $data['user_id'];
        $subscribes = UserChatGroups::findSpam([
            'conditions' => 'user_id = :user_id:',
            'bind' => [
                "user_id" => $user_id
            ]
        ]);
        $toRet = [];
        foreach ($subscribes as $subscribe) {
            $channel = $subscribe->getRelated('Groups', [
                'columns' => Groups::PUBLIC_COLUMNS
            ]);
            if($channel)
            {
                $item=['infos' => $channel];
                $item['is_admin'] = $subscribe->isAdmin();
                array_push($toRet, $item);
            }

        }
        return $toRet;
    }

    /**
     * Get channels of user
     *
     * @param $data array
     * @return array
     */
    public function getFromHidden($data)
    {
        $user_id = $data['user_id'];
        $subscribes = UserChatGroups::findHidden([
            'conditions' => 'user_id = :user_id:',
            'bind' => [
                "user_id" => $user_id
            ]
        ]);
        $toRet = [];
        foreach ($subscribes as $subscribe) {
            $channel = $subscribe->getRelated('Groups', [
                'columns' => Groups::PUBLIC_COLUMNS
            ]);
            if($channel)
            {
                $item=['infos' => $channel];
                $item['is_admin'] = $subscribe->isAdmin();
                array_push($toRet, $item);
            }

        }
        return $toRet;
    }



    /**
     * Toogle to spam
     *
     * @param $data
     * @return bool
     */
    public function toogleToSpam($data)
    {
        try {

            if (!isset($data["group_id"]) || !is_integer($data["group_id"])) {
                throw new Http400Exception(_('Missing group id'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $group_id = $data["group_id"];
            $user_id = $data["user_id"];
            $group = Groups::findFirst($group_id);
            if (!$group)
                throw new Http400Exception(_('Unable to access to the group'), AbstractHttpException::BAD_REQUEST_CONTENT);



            $subscribe = $this->getUserGroupLine($user_id, $group_id);
            $subscribe->spam();

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Toogle to spam
     *
     * @param $data
     * @return bool
     */
    public function toogleToHidden($data)
    {
        try {

            if (!isset($data["group_id"]) || !is_integer($data["group_id"])) {
                throw new Http400Exception(_('Missing group id'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $group_id = $data["group_id"];
            $user_id = $data["user_id"];
            $group = Groups::findFirst($group_id);
            if (!$group)
                throw new Http400Exception(_('Unable to access to the group'), AbstractHttpException::BAD_REQUEST_CONTENT);



            $subscribe = $this->getUserGroupLine($user_id, $group_id);
            $subscribe->hidden();

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    public function isUserOnGroup($user_id, $group_id)
    {
        $user_line = UserChatGroups::findFirst([
            'conditions' => 'user_id = :user_id: AND group_id = :group_id:',
            'bind' => [
                'user_id' => $user_id,
                'group_id' => $group_id
            ]
        ]);
        if (!$user_line) {
            return false;
        }

        return true;
    }

    /**
     * @param $user_id
     * @param $group_id
     * @return UserChatGroups|null
     */
    public function getUserGroupLine($user_id, $group_id)
    {
        $user_line = UserChatGroups::findFirst([
            'conditions' => 'user_id = :user_id: AND group_id = :group_id:',
            'bind' => [
                'user_id' => $user_id,
                'group_id' => $group_id
            ]
        ]);
        if (!$user_line)
            return null;
        return $user_line;
    }

}