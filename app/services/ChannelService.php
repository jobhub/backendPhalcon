<?php

namespace App\Services;

use App\Controllers\AbstractHttpException;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Libs\SupportClass;
use App\Models\ChannelMessages;
use App\Models\Channels;
use App\Models\ChannelUsersSubscriber;
use App\Models\Userinfo;
use App\Models\Users;

use App\Services\ServiceExtendedException;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ChannelService extends AbstractService
{

    const ERROR_UNABLE_TO_CREATE_CHANNEL = 13001;

    const ERROR_UNABLE_TO_GET_CHANNEL = 13002;

    const ERROR_UNABLE_TO_SUBSCRIBE_CHANNEL = 13003;

    const ERROR_UNABLE_TO_SEND_MESSAGE_TO_CHANNEL = 13004;

    const ERROR_UNABLE_TO_ACCESS_CHANNEL = 13005;

    const ERROR_TRANSACTION = 13005;

    /**
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function createChannel($user_id, $data)
    {
        try {
            if (is_null($data["name"]) || is_null($data["status"]) || is_null($data["is_public"])) {
                throw new Http400Exception(_('Missing channel data'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            if (strlen(trim($data["name"])) == 0 || strlen(trim($data["status"])) == 0 || !is_bool($data["is_public"])) {
                throw new Http400Exception(_('Wrong data'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $manager = new TxManager();
            $transaction = $manager->get();

            $channel = new Channels();
            $channel->setTransaction($transaction);

            $channel->setName($data['name'])
                ->setCreatorId($user_id)
                ->setStatus($data['status']);
            $channel->setIsPublic($data['is_public']);
            if ($channel->save() === false) {


                $transaction->rollback(
                    'Cannot save Channels'
                );
            }

            $subscriber = new ChannelUsersSubscriber();
            $subscriber->setTransaction($transaction);

            $subscriber->setUserId($user_id)
                ->setIsAdmin(true);
            $subscriber->setChannelId($channel->getId());
            if ($subscriber->save() === false) {
                $transaction->rollback(
                    'Cannot save ChannelUsersSubscriber '
                );
            }

            $transaction->commit();

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return true;
    }

    /**
     * @param $name
     * @return array
     */
    public function findChannel($name)
    {
        try {
            if (is_null($name))
                return [];
            $canonical_name = strtolower($name);
            $channels = Channels::find(
                [
                    'conditions' => 'canonical_name LIKE :name: and is_public = :type:',
                    'bind' => [
                        "name" => '%' . $canonical_name . '%',
                        'type' => 1
                    ],
                    'columns' => Channels::PUBLIC_COLUMNS
                ]
            );
            if (!$channels)
                return [];
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $channels->toArray();
    }

    /**
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function subscribeToPublicChannel($user_id, $data)
    {
        try {
            $channel_id = $data['channel_id'];
            $existing = self::isUserSubscribedOnChannel($channel_id, $user_id);
            if ($existing)
                throw new Http400Exception(_('User already subscribe on this channel'), self::ERROR_UNABLE_TO_SUBSCRIBE_CHANNEL);

            $channel = Channels::findFirst([
                'conditions' => 'id = :channel_id: and is_public = :type:',
                'bind' => [
                    "channel_id" => $channel_id,
                    'type' => 1
                ]
            ]);
            if (!$channel)
                throw new Http400Exception(_('Unable to access to this channel'), self::ERROR_UNABLE_TO_GET_CHANNEL);
            $subscriber = new ChannelUsersSubscriber();
            $subscriber->setUserId($user_id)
                ->setChannelId($channel_id);
            $subscriber->create();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * add many users to public of private channels
     *
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function addUsersToChannel($user_id, $data)
    {
        try {
            $channel_id = $data['channel_id'];
            $channel = Channels::findFirst([
                'conditions' => 'id = :channel_id:',
                'bind' => [
                    "channel_id" => $channel_id
                ]
            ]);
            if (!$channel)
                throw new Http400Exception(_('Unable to access to this channel : channel not found'), self::ERROR_UNABLE_TO_GET_CHANNEL);
            $user_ids = $data['users'];
            if (!$channel->isPublic()) {
                $subscribe = $this->getUserSubscribeLine($channel_id, $user_id);
                if (is_null($subscribe) || !$subscribe->isAdmin())
                    throw new Http400Exception(_('Unable to access to this channel : user must be the creator of the channel'), self::ERROR_UNABLE_TO_ACCESS_CHANNEL);
            }
            foreach ($user_ids as $id) {
                $exist_user = Users::isUserExist($id);
                if ($exist_user) {
                    $existing = self::isUserSubscribedOnChannel($channel_id, $id);
                    if (!$existing) {
                        $subscriber = new ChannelUsersSubscriber();
                        $subscriber->setUserId($id)
                            ->setChannelId($channel_id);
                        $subscriber->create();
                    }
                }
            }

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * delete many users to public of private channels
     *
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function removeUsersToChannel($user_id, $data)
    {
        try {
            $channel_id = $data['channel_id'];
            $channel = Channels::findFirst([
                'conditions' => 'id = :channel_id:',
                'bind' => [
                    "channel_id" => $channel_id
                ]
            ]);
            if (!$channel)
                throw new Http400Exception(_('Unable to access to this channel : channel not found'), self::ERROR_UNABLE_TO_GET_CHANNEL);
            $user_ids = $data['users'];
            if (!$channel->isPublic()) {
                $subscribe = $this->getUserSubscribeLine($channel_id, $user_id);
                if (is_null($subscribe) || !$subscribe->isAdmin())
                    throw new Http400Exception(_('Unable to access to this channel : user must be the creator of the channel'), self::ERROR_UNABLE_TO_ACCESS_CHANNEL);
            }
            foreach ($user_ids as $id) {
                $subscribe = self::getUserSubscribeLine($channel_id, $id);
                if (!is_null($subscribe)) {
                    $subscribe->delete();
                }
            }

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * add many users to admin or remove to public of private channels
     *
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function manageAdminUserChannel($user_id, $data)
    {
        $is_admin = true;
        if(!is_null($data['action']) && is_bool($data['action']))
            $is_admin = $data['action'];
        $channel_id = $data['channel_id'];
        try {
            $channel = Channels::findFirst([
                'conditions' => 'id = :channel_id:',
                'bind' => [
                    "channel_id" => $channel_id
                ]
            ]);
            if (!$channel)
                throw new Http400Exception(_('Unable to access to this channel : channel not found'), self::ERROR_UNABLE_TO_GET_CHANNEL);
            $user_ids = $data['users'];
            if ($channel->getCreatorId() != $user_id) {
                throw new Http400Exception(_('Unable to access to this channel : user must be the creator of the channel'), self::ERROR_UNABLE_TO_ACCESS_CHANNEL);
            }
            foreach ($user_ids as $id) {
                $subscribe = self::getUserSubscribeLine($channel_id, $id);
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
     * add many users to admin or remove to public of private channels
     *
     * @param $user_id
     * @param $channel_id
     * @return bool
     */
    public function deleteChannelHistory($user_id, $channel_id)
    {
        try {
            $channel = Channels::findFirst([
                'conditions' => 'id = :channel_id:',
                'bind' => [
                    "channel_id" => $channel_id
                ]
            ]);
            if (!$channel)
                throw new Http400Exception(_('Unable to access to this channel : channel not found'), self::ERROR_UNABLE_TO_GET_CHANNEL);
            if ($channel->getCreatorId() != $user_id) {
                throw new Http400Exception(_('Unable to access to this channel : user must be the creator of the channel'), self::ERROR_UNABLE_TO_ACCESS_CHANNEL);
            }
            $messages = ChannelMessages::find([
                'conditions' => 'channel_id = :channel_id:',
                'bind' => [
                    "channel_id" => $channel_id
                ]
            ]);
            foreach ($messages as $message) {
                $message->delete();
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function sendMessageToChannel($user_id, $data)
    {
        try {
            if (is_null($data["channel_id"]) || is_null($data["body"])) {
                throw new Http400Exception(_('Bad request content'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $channel_id = $data['channel_id'];
            $channel = Channels::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    "id" => $channel_id
                ]
            ]);
            if (!$channel)
                throw new Http400Exception(_('Unable to access to this channel'), self::ERROR_UNABLE_TO_GET_CHANNEL);

            $subscribe = self::getUserSubscribeLine($channel_id, $user_id);
            if (!$subscribe)
                throw new Http400Exception(_('Unable to access this channel'), self::ERROR_UNABLE_TO_GET_CHANNEL);
            if (!$channel->isPublic() && !$subscribe->isAdmin())
                throw new Http400Exception(_('The user does not have the right to write to channel'), self::ERROR_UNABLE_TO_SEND_MESSAGE_TO_CHANNEL);
            $message = new ChannelMessages();
            $message->setSenderId($user_id);
            $message->setContent($data['body']);
            $message->setType($data['type']);
            $message->setChannelId($channel_id);
            $message->create();

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * @param $user_id
     * @param $page
     * @param $data
     * @return array
     */
    public function getChannelMessages($user_id, $data, $page)
    {
        try {
            if (is_null($data["channel_id"])) {
                throw new Http400Exception(_('Bad request content : required channel_id & page data'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $channel_id = $data['channel_id'];
            $channel = Channels::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    "id" => $channel_id
                ]
            ]);
            if (!$channel)
                throw new Http400Exception(_('Unable to access to this channel'), self::ERROR_UNABLE_TO_GET_CHANNEL);

            $existing = self::isUserSubscribedOnChannel($channel_id, $user_id);
            if (!$existing)
                throw new Http400Exception(_('Unable to access this channel : user not subscribed'), self::ERROR_UNABLE_TO_SUBSCRIBE_CHANNEL);
            $offset = ($page - 1) * ChannelMessages::DEFAULT_RESULT_PER_PAGE;
            $messages = ChannelMessages::find([
                'order' => 'create_at ASC',
                'limit' => ChannelMessages::DEFAULT_RESULT_PER_PAGE,
                'conditions' => 'channel_id = :channel_id:',
                'offset' => $offset, // offset of result
                'bind' => [
                    "channel_id" => $channel_id
                ],
                'columns' => ChannelMessages::PUBLIC_COLUMNS
            ]);
            $toRet = [];
            foreach ($messages as $message) {
                $user = Users::findFirst($message['sender_id']);
                $item = $message->toArray();
                $item['sender'] = $user->getUserShortInfo();
                array_push($toRet, $item);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $toRet;
    }

    /**
     * Get channels of user
     *
     * @param $user_id integer
     * @return array
     */
    public function getUserChannels($user_id)
    {
        $subscribes = ChannelUsersSubscriber::find([
            'conditions' => 'user_id = :user_id:',
            'bind' => [
                "user_id" => $user_id
            ]
        ]);
        $toRet = [];
        $toRet['userChannels'] = [];
        $toRet['subscribedChannels'] = [];
        foreach ($subscribes as $subscribe) {
            $channel = $subscribe->getRelated('Channels', [
                'columns' => Channels::PUBLIC_COLUMNS
            ]);
            if ($channel->creator_id == $user_id) {
                //$channel['is_admin'] = $subscribe->isAdmin();
                array_push($toRet['userChannels'], $channel);
            } else {
                //$channel['is_admin'] = $subscribe->isAdmin();
                $item['channel_info'] = $channel;
                $item['user_role']['is_admin'] = $subscribe->isAdmin();
                array_push($toRet['subscribedChannels'], $item);
            }
        }
        return $toRet;
    }

    /**
     * get Users of a channel
     *
     * @param $user_id
     * @param $channel_id
     * @return array
     */
    public function getUsersChannel($user_id, $channel_id)
    {
        if (is_null($channel_id)) {
            throw new Http400Exception(_('Bad request content : required channel_id'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        $channel = Channels::findFirst($channel_id);
        if (!$channel)
            throw new Http400Exception(_('Unable to access to this channel'), self::ERROR_UNABLE_TO_GET_CHANNEL);
        $toRet = [];
        $toRet['creator'] = $channel->getRelated('User')->getUserShortInfo();
        $toRet['users'] = [];
        $toRet['admins'] = [];
        $subscribes = $channel->getRelated('ChannelUsersSubscriber', [
            'order' => 'subscribe_at ASC',
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
     * @param $channel_id
     * @param $user_id
     * @return bool
     */
    public function isUserSubscribedOnChannel($channel_id, $user_id)
    {
        $existing = ChannelUsersSubscriber::findFirst([
            'conditions' => 'user_id = :user_id: AND channel_id = :channel_id:',
            'bind' => [
                "user_id" => $user_id,
                "channel_id" => $channel_id,
            ]
        ]);
        if (!$existing)
            return false;
        return true;
    }

    /**
     * @param $channel_id
     * @param $user_id
     * @return ChannelUsersSubscriber|\Phalcon\Mvc\Model\ResultInterface
     */
    public function getUserSubscribeLine($channel_id, $user_id)
    {
        $existing = ChannelUsersSubscriber::findFirst([
            'conditions' => 'user_id = :user_id: AND channel_id = :channel_id:',
            'bind' => [
                "user_id" => $user_id,
                "channel_id" => $channel_id,
            ]
        ]);
        if (!$existing)
            return null;
        return $existing;
    }
}
