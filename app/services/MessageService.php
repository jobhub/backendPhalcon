<?php

namespace App\Services;

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
    const ERROR_UNABLE_SEND_MSG = 11001;

    /**
     * Send message
     *
     * @param array $data
     * @return boolean $success
     */
    public function sendMessage($data) {
        try {

            $chatHitory = $this->privateChatService->getChatHistory($data['sender'], $data['reciever'], true);
            $msg = new Message();
            $msg->setSender($data["sender"])
                    ->setContent($data["content"])
                    ->setChatHistId($chatHitory->getId())
                    ->setMessageType($data["type"])
                    ->create();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e, $this->logger);
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

            $chatHistory = $this->privateChatService->getChatHistory($data['sender'], $data['reciever']);

            if (!$chatHistory) {
                return [];
            }

            $messages = $chatHistory->getRelated('messages', [
                'order' => 'creationDate ASC',
                //'limit' => 2,
                'where' => 'id > 30',
                'offset' => $data['page'], // offset of result
                'count' => 'id'
            ]);
            $result = $messages->toArray();
            return $result;
        } catch (\PDOException $e) {
            $this->logger->critical(
                    $e->getMessage() . ' ' . $e->getCode()
            );
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

   

}
