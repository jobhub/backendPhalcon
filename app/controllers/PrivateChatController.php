<?php

namespace App\Controllers;

use App\Controllers\AbstractController;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\AbstractService;
use App\Services\ServiceException;
use App\Services\UserService;

class PrivateChatController extends AbstractController {

    /**
     * Toggle spam discussion
     *
     * @return  string
     */
    public function spamTogglePrivateChatAction() {
        try {
            $user_id = $this->getUserId();
            $data = json_decode($this->request->getRawBody(), true);
            $this->privateChatService->togglePrivateChatToSpam($user_id, $data['messaging_id']);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return parent::successResponse('successfully complete');
    }

    /**
     * Toggle spam discussion
     *
     * @return  string
     */
    public function hiddenTogglePrivateChatAction() {
        try {
            $user_id = $this->getUserId();
            $data = json_decode($this->request->getRawBody(), true);
            $this->privateChatService->togglePrivateChatToHidden($user_id, $data['messaging_id']);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return parent::successResponse('successfully complete');
    }
}
