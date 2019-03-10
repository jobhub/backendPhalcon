<?php

namespace App\Controllers;

use App\Controllers\AbstractController;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\AbstractService;
use App\Services\ServiceException;
use App\Services\UserService;

class MessageController extends AbstractController {

    /**
     * send message to other user
     * Returns user list
     *
     * @return array
     */
    public function sendMessageAction() {
        $data = json_decode($this->request->getRawBody(), true);
        $data["sender"] = $this->getUserId();
        try {
            $this->messageService->sendMessage($data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::chatResponce('');
    }
    
    /**
     * send message to other user
     * Returns user list
     *
     * @return array
     */
    public function getChatBoxAction() {
        $data = json_decode($this->request->getRawBody(), true);
        $data["sender"] = $this->getUserId();
        try {
           $response = $this->messageService->getMessages($data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return $response;
    }

    /**
     * send message to other user
     * Returns user list
     *
     * @return array
     */
    public function getUnReadChatBoxAction() {
        try {
            $response = $this->privateChatService->getUnReadMessages($this->getUserId());
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return $response;
    }

    /**
     * send message to other user
     * Returns user list
     *
     * @return array
     */
    public function setAllToReadAction() {
        $user_id = $this->getUserId();
        $data = json_decode($this->request->getRawBody(), true);
        $data["sender"] = $user_id;
        try {
            $this->messageService->setAllMessageToReaded($data, false); // using same message service with group chat
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('Done');
    }
 

}
