<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ChatHistory;
use App\Models\Message;
use App\Models\PrivateChat;
use App\Models\Userinfo;

/**
 * business logic for users
 *
 * Class UsersService
 */
class PrivateChatService extends AbstractService {

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
    public function getPrivateChat($user_id, $related_user, $createIfNoExist = false) {
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
    public function getChatHistory($user_id, $related_user, $createIfNoExist = false) {
        try {
            $chatBox = $this->getPrivateChat($user_id, $related_user, $createIfNoExist);
            if (!$chatBox)
                return null;
            $this->logger->log(
               'user '. $user_id.' send message to user '. $related_user
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
    public function getUserPublicChats($user_id, $is_spam = false, $is_hidden = false) {
        try {
            if($is_hidden){
                $chatBox = PrivateChat::findHidden(
                    [
                        'conditions' => 'user_id = :user:',
                        'bind' => [
                            "user" => $user_id,
                        ],
                    ]
                );
            }
            elseif($is_spam){
                $chatBox = PrivateChat::findSpam(
                    [
                        'conditions' => 'user_id = :user:',
                        'bind' => [
                            "user" => $user_id,
                        ],
                    ]
                );
            }
            else{
                $chatBox = PrivateChat::findEnabled(
                    [
                        'conditions' => 'user_id = :user:',
                        'bind' => [
                            "user" => $user_id,
                        ],
                    ]
                );
            }
            $toRet = [];
            $ob = [];
            foreach ($chatBox as $value) {
                $this->logger->log(
                    'chat id '. $value->getId()
                );
                $user = $value->getRelated('relatedUser');
                $ob['user'] =$user->getRelated('Userinfo', [
                    'columns' => Userinfo::publicColumns
                ]);
                $ob['msg'] = $value->getRelated('Chathistory')->getRelated('Messages', [
                    'order' => 'create_at DESC',
                    'limit' => 1,
                    'columns' => Message::PUBLIC_COLUMNS
            ]);
                array_push($toRet, $ob);
            }
            return $toRet;
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
    public function togglePrivateChatToSpam($user_id, $chat_id){
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
            if(!$chatBox){
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
    public function togglePrivateChatToHidden($user_id, $chat_id){
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
            if(!$chatBox){
                throw new Http400Exception('unable get private chat : missing id', self::ERROR_GET_CHAT_HIS);
            }
            $chatBox->hidden();
            return true;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     *  set all unread messages to read
     *
     * @param $user_id boolean $related_user integer,
     * @param $related_user integer,
     * @return boolean
     */
    public function setAllMessageToReaded($user_id, $related_user) {
        try {
            $chatBox = $this->getPrivateChat($user_id, $related_user, false);
            if (!$chatBox)
                return true;
            $msgs =$chatBox->getRelated('Chathistory')->getRelated('Messages', [ 
                'conditions' => 'is_readed = :isReaded:',
                'bind' => [
                                    "isReaded" => 0,
                                ]
            ]); 
            foreach ($msgs as $value) {
                $value->setIsReaded(true);
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
