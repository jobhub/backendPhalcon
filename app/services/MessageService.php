<?php

namespace App\Services;

use App\Controllers\AbstractHttpException;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http401Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Models\Groups;
use App\Models\Message;
use App\Models\ChatHistory;

/**
 * business logic for users
 *
 * Class UsersService
 */
class MessageService extends AbstractService
{

    /** Unable to get message */
    const ERROR_UNABLE_GET_DATA = 11001;

    /** Unable to send message */
    const ERROR_UNABLE_SEND_MSG = 11002;


    const ERROR_NOT_ALLOWED = 11003;

    /**
     * Send message
     *
     * @param array $data
     * @param $is_group_msg
     * @return boolean $success
     */
    public function sendMessage($data, $is_group_msg = false)
    {
        try {
            if (!isset($data["body"]) || empty(trim($data["body"]))) {
                throw new Http400Exception(_('Bad request content'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $chatHistory = $this->getChatHistoryByDataType($data, $is_group_msg);
            if (is_null($chatHistory)) {
                return [];
            }
            if (is_null($chatHistory)) {
                throw new Http400Exception('unable to get message service : missing id', self::ERROR_UNABLE_SEND_MSG);
            }
            $msg = new Message();

            if (isset($data['answer_of']) && is_integer($data['answer_of'])) {
                // the message is answer of other message
                $other_msg = $data['answer_of'];
                $exist = Message::findFirst([
                    'conditions' => 'chat_hist_id = :chat_hist_id: AND id = :id:',
                    'bind' => [
                        "chat_hist_id" => $chatHistory->getId(),
                        "id" => $other_msg
                    ]
                ]);
                if ($exist)
                    $msg->setAnswerOf($other_msg);
            }


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


    public function uploadFiles()
    {
        #check if there is any file
        if ($this->request->hasFiles() == true) {
            $uploads = $this->request->getUploadedFiles();
            $isUploaded = false;
            #do a loop to handle each file individually
            foreach ($uploads as $upload) {
                #define a “unique” name and a path to where our file must go
                $path = ‘temp / ’ . md5(uniqid(rand(), true)) . ’ - ’ . strtolower($upload->getname());
                #move the file and simultaneously check if everything was ok
                ($upload->moveTo($path)) ? $isUploaded = true : $isUploaded = false;
            }
            #if any file couldn’t be moved, then throw an message
            if ($isUploaded)
                return true;
            return false;
        }
    }

    public function deleteMessage($data, $is_group_msg = false)
    {

            $id_msg = $data['msg_id'];
            try {
            $message = Message::findFirst($id_msg);
            if (!$message)
                throw new Http400Exception('unable to get message service : missing id', self::ERROR_UNABLE_SEND_MSG);
           $data = $message->getArrayReaded();
           var_dump($data);
            } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get message
     *
     * @param array $data
     * @param  boolean $is_group_msg
     * @return array $messages
     */
    public function getMessages($data, $is_group_msg = false)
    {
        try {
            $chatHistory = $this->getChatHistoryByDataType($data, $is_group_msg);
            if (is_null($chatHistory)) {
                return [];
            }

            if (isset($data["page"]) && is_integer($data["page"]))
                $page = $data["page"];
            else
                $page = 1;
            $page = $page > 0 ? $page : 1;
            $offset = ($page - 1) * Message::DEFAULT_RESULT_PER_PAGE;
            $messages = Message::find([
                'order' => 'create_at ASC',
                'limit' => Message::DEFAULT_RESULT_PER_PAGE,
                'conditions' => 'chat_hist_id = :chat_hist_id:',
                'bind' => [
                    "chat_hist_id" => $chatHistory->getId()
                ],
                'offset' => $offset, // offset of result
                'columns' => Message::PUBLIC_COLUMNS

            ]);
            $result = $messages->toArray();
            return $result;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Send message
     *
     * @param array $data
     * @param $is_group_msg
     * @return boolean $success
     */
    public function deleteHistory($data, $is_group_msg = false)
    {
        try {
            if (!isset($data["body"]) || empty(trim($data["body"]))) {
                throw new Http400Exception(_('Bad request content'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $chatHistory = $this->getChatHistoryByDataType($data, $is_group_msg);
            if (is_null($chatHistory)) {
                return [];
            }
            if (is_null($chatHistory)) {
                throw new Http400Exception('unable to get message service : missing id', self::ERROR_UNABLE_SEND_MSG);
            }
            $messages = Message::find([
                'conditions' => 'chat_hist_id = :chat_hist_id:',
                'bind' => [
                    "chat_hist_id" => $chatHistory->getId()
                ]

            ]);
            foreach ($messages as $message) {
                $messages->delete();
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * @param $data
     * @param $is_group_msg
     * @return ChatHistory
     */
    public function getChatHistoryByDataType($data, $is_group_msg)
    {
        if (isset($data["group_id"]) && !is_null($data["group_id"]) && $is_group_msg) {
            $write = $this->groupService->isUserOnGroup($data['sender'], $data["group_id"]);
            if (!$write)
                throw new Http401Exception('User not allowed to access on this group', self::ERROR_NOT_ALLOWED);
            $chatHistory = $this->chatHistoryService->getChatHistoryFromGroup($data['group_id']);
        } elseif (isset($data["other_user_id"]) && !empty($data["other_user_id"]) && !$is_group_msg) {
            $chatHistory = $this->privateChatService->getChatHistory($data['sender'], $data['other_user_id']);
        } else {
            throw new Http400Exception(_('Bad request content : missing messaging id'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        return $chatHistory;
    }


}
