<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Libs\SupportClass;
use App\Models\ChatHistory;
use App\Models\Message;
use App\Models\PrivateChat;
use App\Models\Userinfo;
use App\Models\Users;

/**
 * business logic for users
 *
 * Class UsersService
 */
class PrivateChatService extends AbstractService
{

    const ADDED_CODE_NUMBER = 28000;

    /** Unable to find user */
    const ERROR_UNABLE_TO_FIND_CHAT = 1 + self::ADDED_CODE_NUMBER;

    /** Unable to get chat history  */
    const ERROR_GET_CHAT_HIS = 2 + self::ADDED_CODE_NUMBER;

    /**
     * Returns users chat history (private chanel)
     *
     * @param $user_id boolean
     * @param $related_user integer
     * @param $createIfNoExist boolean
     * @return PrivateChat $chatBox
     */
    public function getPrivateChat($user_id, $related_user, $createIfNoExist = false)
    {
        try {
            $chatBox = PrivateChat::findFirst(
                [
                    'conditions' => 'user_id = :user: and related_user_id = :related_user:',
                    'bind' => [
                        "user" => $user_id,
                        "related_user" => $related_user
                    ],
                ]
            );
            if (!$chatBox && $createIfNoExist) {
                // create a private chat for these users
                $chatBox = new PrivateChat();
                $chatHis = $this->chatHistoryService->createChatHistory();
                $chatBox->setUserId($user_id)
                    ->setRelatedUserId($related_user)
                    ->setChatHistId($chatHis->getId())
                    ->create();
                $chatBox2 = new PrivateChat();
                $chatBox2->setUserId($related_user)
                    ->setRelatedUserId($user_id)
                    ->setChatHistId($chatHis->getId())
                    ->create();
                return $chatBox;
            }

            return $chatBox;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns chatHistory of two users
     *
     * @param $user_id boolean
     * @param $related_user integer
     * @param $createIfNoExist boolean
     * @return ChatHistory $chatBox
     */
    public function getChatHistory($user_id, $related_user, $createIfNoExist = false)
    {
        try {
            $chatBox = $this->getPrivateChat($user_id, $related_user, $createIfNoExist);
            if (!$chatBox)
                return null;
            $this->logger->log(
                'user ' . $user_id . ' send message to user ' . $related_user
            );
            return $chatBox->getRelated('Chathistory');
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e, $this->logger);
        }
    }

    /**
     * Returns all user chatHistory
     *
     * @param $user_id integer
     * @param $is_spam boolean "get only spam discussions"
     * @param $is_hidden boolean "get only spam hidden"
     * @return array
     */
    public function getUserPublicChats($user_id, $is_spam = false, $is_hidden = false)
    {
        try {
            if ($is_hidden) {
                $chatBox = $this->getHiddenChat($user_id);
            } elseif ($is_spam) {
                $chatBox = $this->getHiddenChat($user_id);
            } else {
                $chatBox = $this->getPublicChat($user_id);
            }

            //var_dump($chatBox);
            $toRet = [];
            $ob = [];
            foreach ($chatBox as $value) {
                $ob = $this->formatPrivateChat($user_id, $value);
                array_push($toRet, $ob);
            }
            return $toRet;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns all user chatHistory
     *
     * @param $user_id integer
     * @param $is_spam boolean "get only spam discussions"
     * @param $is_hidden boolean "get only spam hidden"
     * @return array
     */
    public function getUnReadMessages($user_id, $is_spam = false, $is_hidden = false)
    {
        try {
            /* if ($is_hidden) {
                 $chatBox = $this->getHiddenChat($user_id);
             } elseif ($is_spam) {
                 $chatBox = $this->getHiddenChat($user_id);
             } else {
                 $chatBox = $this->getPublicChat($user_id);
             }*/

            //var_dump($chatBox);
            $chatBox = $this->getUnReadChat($user_id);
            $toRet = [];
            $ob = [];
            foreach ($chatBox as $value) {
               $ob = $this->formatPrivateChat($user_id, $value);
               array_push($toRet, $ob);
            }
            return $toRet;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function formatPrivateChat($user_id, $value)
    {
        $ob = [];
        $user = Users::findFirst($value['related_user_id']);
        $ob['user'] = $user->getRelated('Userinfo', [
            'columns' => Userinfo::shortColumns
        ]);
        $ob['msg'] = ChatHistory::findFirst($value['chat_hist_id'])->getRelated('Messages', [
            'order' => 'create_at DESC',
            'limit' => 1,
            'columns' => Message::PUBLIC_COLUMNS
        ]);
        $ob['unread'] = Message::countUnRead($user_id, $value['chat_hist_id']);
        return $ob;
    }

    /**
     * Return true if success
     *
     * @param $user_id integer
     * @param $chat_id boolean "chat history id"
     * @return boolean
     */
    public function togglePrivateChatToSpam($user_id, $chat_id)
    {
        try {
            $chatBox = PrivateChat::findFirst(
                [
                    'conditions' => 'user_id = :user: and chat_hist_id = :chat_id:',
                    'bind' => [
                        "user" => $user_id,
                        "chat_id" => $chat_id
                    ],
                ]
            );
            if (!$chatBox) {
                throw new Http400Exception('unable get private chat : missing id', self::ERROR_GET_CHAT_HIS);
            }
            $chatBox->spam();
            return true;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Return true if success
     *
     * @param $user_id integer
     * @param $chat_id boolean "chat history id"
     * @return boolean
     */
    public function togglePrivateChatToHidden($user_id, $chat_id)
    {
        try {
            $chatBox = PrivateChat::findFirst(
                [
                    'conditions' => 'user_id = :user: and chat_hist_id = :chat_id:',
                    'bind' => [
                        "user" => $user_id,
                        "chat_id" => $chat_id
                    ],
                ]
            );
            if (!$chatBox) {
                throw new Http400Exception('unable get private chat : missing id', self::ERROR_GET_CHAT_HIS);
            }
            $chatBox->hidden();
            return true;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getSpamChat($user_id)
    {
        return $this->db->fetchAll('SELECT user_id, related_user_id, chat_hist_id FROM "privateChat" as p INNER JOIN "chatHistory" as c ON p.chat_hist_id = c.id  WHERE user_id = ' . $user_id . ' AND deleted != true AND is_hidden != true  AND is_spam = true ORDER BY c.last_modification_date DESC
        ');
    }

    public function getHiddenChat($user_id)
    {
        return $this->db->fetchAll('SELECT user_id, related_user_id, chat_hist_id FROM "privateChat" as p INNER JOIN "chatHistory" as c ON p.chat_hist_id = c.id  WHERE user_id = ' . $user_id . ' AND deleted != true AND is_hidden = true  AND is_spam != true ORDER BY c.last_modification_date DESC
        ');
    }


    public function getPublicChat($user_id)
    {
        return $this->db->fetchAll('SELECT user_id, related_user_id, chat_hist_id FROM "privateChat" as p INNER JOIN "chatHistory" as c ON p.chat_hist_id = c.id  WHERE user_id = ' . $user_id . ' AND deleted != true AND is_hidden != true  AND is_spam != true ORDER BY c.last_modification_date DESC
        ');
    }

    public function getUnReadChat($user_id)
    {
        return $this->db->fetchAll('
            SELECT DISTINCT user_id, related_user_id, p.chat_hist_id, c.last_modification_date FROM "privateChat" as p 
            INNER JOIN "chatHistory" as c ON p.chat_hist_id = c.id  
            INNER JOIN  "message" as m ON m.chat_hist_id = c.id 
            WHERE p.user_id = ' . $user_id . ' AND NOT (' . $user_id . ' = ANY (m.readed_users)) AND p.deleted != true AND is_hidden != true  AND is_spam != true 
            ORDER BY c.last_modification_date DESC
     ');
    }

    /**
     *  set all unread messages to read
     *
     * @param $user_id boolean $related_user integer,
     * @param $related_user integer,
     * @return boolean
     */
    public function setAllMessageToReaded($user_id, $related_user)
    {
        try {
            $chatBox = $this->getPrivateChat($user_id, $related_user, false);
            if (!$chatBox)
                return true;
            $msgs = Message::findUnreaded($user_id, $chatBox->getId());
            foreach ($msgs as $value) {
                $readed = SupportClass::to_php_array($value->getReadedUsers());
                array_push($readed, $user_id);
                $value->setReadedUsers($readed);
                $value->update();
            }

        } catch (\PDOException $e) {
            $this->logger->critical(
                $e->getMessage()
            );
            throw new ServiceException($e->getMessage(), $e->getCode(), $e, $this->logger);
        }
        return true;
    }

}
