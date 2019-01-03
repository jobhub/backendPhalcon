<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Models\Message;
use App\Models\ChatHistory;

/**
 * business logic for users
 *
 * Class UsersService
 */
class MessageService extends AbstractService {

    /** Unable to get message */
    const ERROR_UNABLE_GET_DATA = 11001;
    
    /** Unable to send message */
    const ERROR_UNABLE_SEND_MSG = 11002;

    /**
     * Send message
     *
     * @param array $data
     * @return boolean $success
     */
    public function sendMessage($data) {
        try {
            if(is_null($data["sender"]) || is_null($data["body"])) {
                throw new Http400Exception(_('Bad request content'), Http500Exception::BAD_REQUEST_CONTENT);
            }
            if(!is_null($data["message_id"])){
                $chatHistory = $this->chatHistoryService->getChat($data['message_id']);
            }elseif(!is_null($data["user_reciever_id"])){
                $chatHistory = $this->privateChatService->getChatHistory($data['sender'], $data['user_reciever_id'], true);
            }else{
                throw new Http400Exception(_('Bad request content : missing reciever'), Http500Exception::BAD_REQUEST_CONTENT);
            }
            if(is_null($chatHistory)){
                throw new Http400Exception('unable to get service : missing id', self::ERROR_UNABLE_SEND_MSG);
            }
            $msg = new Message();
            $msg->setSenderId($data["sender"])
                    ->setContent($data["body"])
                    ->setChatHistId($chatHistory->getId())
                    ->setMessageType($data["type"])
                    ->create();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Send message
     *
     * @param array $data
     * @return array $messages
     */
    public function getMessages($data) {
        try {
            if(is_null($data["sender"]) || is_null($data["reciever"])) {
                throw new Http500Exception(_('Bad request content'), Http500Exception::BAD_REQUEST_CONTENT);
            }
            $chatHistory = $this->privateChatService->getChatHistory($data['sender'], $data['reciever']);

            if (!$chatHistory) {
                return [];
            }

            $messages = $chatHistory->getRelated('Messages', [
                'order' => 'create_at ASC',
                'limit' => 12,
                //'where' => 'id > 30',
                'offset' => $data['page'], // offset of result
            ]);
            $result = $messages->toArray();
            return $result;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

   

}
