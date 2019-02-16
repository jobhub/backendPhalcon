<?php

namespace App\Services;

use App\Models\ChatHistory;
use App\Models\Groups;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ChatHistoryService extends AbstractService
{
    const ADDED_CODE_NUMBER = 29000;

    /** Unable to create user */
    const ERROR_UNABLE_TO_FIND_CHAT = 1 + self::ADDED_CODE_NUMBER;

    /**
     * Returns users chat history
     * @param  $transaction db transaction
     * @return array
     */
    public function createChatHistory($transaction = null)
    {
        try {
            $chatHist = new ChatHistory();
            if ($transaction != null) {
                $chatHist->setTransaction($transaction);
                if ($chatHist->save() === false) {
                    $transaction->rollback(
                        'Cannot save Group'
                    );
                }
            } else
                $chatHist->save();

            $this->logger->log(
                'creation of chat history id =' . $chatHist->getId()
            );
            return $chatHist;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getChat($id)
    {
        $chatHist = ChatHistory::findFirst($id);
        if (!$chatHist)
            return null;
        return $chatHist;
    }

    /**
     * Get chat history from group_id
     *
     * @param $group_id
     * @return ChatHistory|null
     */
    public function getChatHistoryFromGroup($group_id){
        $group = Groups::findFirst($group_id);
        if (!$group)
            return null;
        $chatHist = $group->getRelated('Chathistory');
        if (!$chatHist)
            return null;
       return $chatHist;
    }

}
