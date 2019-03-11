<?php

namespace App\Services;

use App\Controllers\AbstractHttpException;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http401Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Libs\SupportClass;
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

    const ADDED_CODE_NUMBER = 27000;

    /** Unable to get message */
    const ERROR_UNABLE_GET_DATA = 1 + self::ADDED_CODE_NUMBER;

    /** Unable to send message */
    const ERROR_UNABLE_SEND_MSG = 2 + self::ADDED_CODE_NUMBER;


    const ERROR_NOT_ALLOWED = 3 + self::ADDED_CODE_NUMBER;

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
            $chatHistory = $this->getChatHistoryByDataType($data, $is_group_msg,true);
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

            switch ($data["type"]){
                case Message::TYPE_FORWARD_IMAGE_USER:
                    $image = $this->imageService->getImageById($data["attached_id"],ImageService::TYPE_USER);
                    $msg->setAttachedId($data["attached_id"]);
                    break;
                case Message::TYPE_FORWARD_NEWS:
                    $news = $this->newsService->getNewsById($data["attached_id"]);
                    $msg->setAttachedId($data["attached_id"]);
                    break;
                case Message::TYPE_FORWARD_SERVICE:
                    $service = $this->serviceService->getServiceById($data["attached_id"]);
                    $msg->setAttachedId($data["attached_id"]);
                    break;
                case Message::TYPE_FORWARD_PRODUCT:
                    $product = $this->productService->getProductById($data["attached_id"]);
                    $msg->setAttachedId($data["attached_id"]);
                    break;
            }

            $statut_array = [$data["sender"]];
            $msg->setSenderId($data["sender"])
                ->setContent($data["body"])
                ->setChatHistId($chatHistory->getId())
                ->setMessageType($data["type"])
                ->setReadedUsers(SupportClass::to_pg_array($statut_array))
                ->setReceivedUsers(SupportClass::to_pg_array($statut_array))
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
            $id_msg = $data['message_id'];
            try {
            $message = Message::findFirst($id_msg);
            if (!$message)
                throw new Http400Exception('unable to get message service : missing id', self::ERROR_UNABLE_SEND_MSG);
           $data = $message->getArrayReaded();
            } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $data;
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
                'conditions' => 'chat_hist_id = :chat_hist_id: AND NOT (:user_id: = ANY (deleted_by_users))',
                'bind' => [
                    "chat_hist_id" => $chatHistory->getId(),
                    "user_id" => $data['sender']
                ],
                'offset' => $offset, // offset of result
                'columns' => Message::PUBLIC_COLUMNS

            ]);
            $result = $messages->toArray();
            try {
                return Message::handleMessages($result);
            }catch (\Exception $e){
                echo $e;
            }
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
     * @param $createIfNoExist
     * @return ChatHistory
     */
    public function getChatHistoryByDataType($data, $is_group_msg, $createIfNoExist = false)
    {
        if (isset($data["group_id"]) && !is_null($data["group_id"]) && $is_group_msg) {
            $write = $this->groupService->isUserOnGroup($data['sender'], $data["group_id"]);
            if (!$write)
                throw new Http401Exception('User not allowed to access on this group', self::ERROR_NOT_ALLOWED);
            $chatHistory = $this->chatHistoryService->getChatHistoryFromGroup($data['group_id']);
        } elseif (isset($data["other_user_id"]) && !empty($data["other_user_id"]) && !$is_group_msg) {
            $chatHistory = $this->privateChatService->getChatHistory($data['sender'], $data['other_user_id'],$createIfNoExist);
        } else {
            throw new Http400Exception(_('Bad request content : missing messaging id'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        return $chatHistory;
    }


}
