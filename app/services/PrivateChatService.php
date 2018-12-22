<?php

namespace App\Services;

use App\Models\PrivateChat;

/**
 * business logic for users
 *
 * Class UsersService
 */
class PrivateChatService extends AbstractService {

    /** Unable to create user */
    const ERROR_UNABLE_TO_FIND_CHAT = 11001;

    /**
     * Returns users chat history (private chanel)
     *
     * @return array
     */
    public function getPrivateChat($user1, $user2, $createIfNoChat = false) {
        try {
            $chatBox = PrivateChat::findFirst(
                            [
                                'conditions' => '(user1 = :user1: and user2 = :user2:) or (user1 = :user2: and user2 = :user1:)',
                                'bind' => [
                                    "user1" => $user1,
                                    "user2" => $user2
                                ],
                            ]
            );

            if (!$chatBox && $createIfNoChat) {

                $chatBox = new PrivateChat();
                $chatHis = $this->chatHistoryService->createChatHistory();
                $chatBox->setUser1($user1)
                        ->setUser2($user2)
                        ->setChatHistId($chatHis->getId())
                        ->create();
                return $chatBox;
            }

            return $chatBox;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e, $this->logger);
        }
    }

    /**
     * Returns users chat history
     *
     * @return array
     */
    public function getChatHistory($user1, $user2, $createIfNoexist = false) {
        try {
            $chatBox = $this->getPrivateChat($user1, $user2, $createIfNoexist);
            if (!$chatBox)
                return null;
            return $chatBox->Chathistory;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e, $this->logger);
        }
    }
    
    /**
     * Returns users chat history
     *
     * @return array
     */
    public function getUserDiscutionChat($userId) {
        try {
            $chatBox = PrivateChat::find(
                            [
                                'conditions' => 'user1 = :user: or user2 = :user:',
                                'bind' => [
                                    "user" => $userId, 
                                ],
                            ]
            );
            $toRet = [];
            $ob = [];
            foreach ($chatBox as $value) {
                $user = $value->getRelated('User1');
                if($user->getId() != $userId) {
                    $ob['user'] = $user;
                }else{
                    $ob['user'] = $value->getRelated('User2');
                }
                $ob['msg'] = $value->getRelated('Chathistory')->getRelated('Messages', [ 
                'order' => 'creationDate DESC',
                'limit' => 1, 
            ]); 
                array_push($toRet, $ob);
            }
            return $toRet;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e, $this->logger);
        }
    }

    /**
    * SET ALL MESSAGE IN CHANEL TO READED
     * Returns BOOLEAN
     *
     * @return array
     */
    public function setAllMessageToReaded($user1, $user2) {
        try {
            $chatBox = $this->getPrivateChat($user1, $user2, false);
            if (!$chatBox)
                return true;
            $msgs =$chatBox->getRelated('Chathistory')->getRelated('Messages', [ 
                'conditions' => 'isReaded = :isReaded:',
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
