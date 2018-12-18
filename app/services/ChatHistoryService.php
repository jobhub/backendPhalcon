<?php

namespace App\Services;

use App\Models\ChatHistory;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ChatHistoryService extends AbstractService {

    /** Unable to create user */
    const ERROR_UNABLE_TO_FIND_CHAT = 11001;

    /**
     * Returns users chat history
     *
     * @return array
     */
    public function createChatHistory() {
        try {
            $chatHist = new ChatHistory();
            //$chatHist->save(); 
            if ($chatHist->save() === false) {
                $this->logger->critical(
                        '"Umh, We can\'t store robots right now:" '
                );
            } 
            $this->logger->critical(
                    'Création du chathystory '.    $chatHist->getId()
                );
            return $chatHist;
        } catch (\PDOException $e) {

            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function get($id) {
        $chatHist = ChatHistory::findFirst($id);

        return $chatHist;
    }

}
