<?php

namespace App\Controllers;

use App\Controllers\AbstractController;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\AbstractService;
use App\Services\ServiceException;
use App\Services\UserService;

class ChannelController extends AbstractController {

    /**
     * Get user channel
     *
     * @return  array $discussions
     */
    public function createChannelAction() {
        try {
            $user_id = $this->getUserId();
            $data = json_decode($this->request->getRawBody(), true);
            $this->channelService->createChannel($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return parent::successResponse("successfully created");
    }

    /**
     * Find channel by name
     *
     * @param $name string
     * @return  array $discussions
     */
    public function findChannelAction($name) {
        try {
            $name = $this->request->getQuery('q', null);
            $channels = $this->channelService->findChannel($name);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }

        return $channels;
    }

    /**
     * Subscribe to a chanel
     *
     * @return  array $discussions
     */
    public function subscribePublicChannelAction() {
        try {
            $user_id = $this->getUserId();
            $data = json_decode($this->request->getRawBody(), true);
            $this->channelService->subscribeToPublicChannel($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse("successfully subscribed");
    }

    /**
     * Send message to a chanel
     *
     * @return  array $discussions
     */
    public function sendMessageAction() {
        try {
            $user_id = $this->getUserId();
            $data = json_decode($this->request->getRawBody(), true);
            $this->channelService->sendMessageToChannel($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse("successfully subscribed");
    }

    /**
     * Send message to a chanel
     *
     * @return  array $discussions
     */
    public function getUsersOfChannelAction($data) {
        try {
            $user_id = $this->getUserId();
            $channel_id = $this->request->getQuery('channel_id');
            $data = $this->channelService->getUsersChannel($user_id, $channel_id);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return $data;
    }

    /**
     * Send message to a chanel
     *
     * @return  array $discussions
     */
    public function getUserChannelAction() {
        try {
            $user_id = $this->getUserId();
            $response = $this->channelService->getUserChannels($user_id);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return $response;
    }

    /**
     * Send message to a chanel
     *
     * @return  array $discussions
     */
    public function getChannelMessageAction($params) {
        try {

            $user_id = $this->getUserId();
            $data = [];
            $data['channel_id'] = $this->request->getQuery('channel_id', null);
            $page= $this->request->getQuery('page');
            $page = $page > 0 ? $page: 1 ;
            $response = $this->channelService->getChannelMessages($user_id, $data, $page);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return $response;
    }

    /**
     * Add many users to a chanel
     *
     * @return  array $discussions
     */
    public function addUsersToChannelAction() {
        try {
            $user_id = $this->getUserId();
            $data = json_decode($this->request->getRawBody(), true);
            $this->channelService->addUsersToChannel($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('successfully added');
    }

    /**
     * Add many users to a chanel
     *
     * @return  array $discussions
     */
    public function adminChannelAction() {
        try {
            $user_id = $this->getUserId();
            $data = json_decode($this->request->getRawBody(), true);
            $this->channelService->manageAdminUserChannel($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('successfully done!');
    }

    /**
     * Add many users to a chanel
     *
     * @return  array $discussions
     */
    public function removeUserToChannelAction() {
        try {
            $user_id = $this->getUserId();
            $data = json_decode($this->request->getRawBody(), true);
            $this->channelService->removeUsersToChannel($user_id, $data);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('successfully removed');
    }

    /**
     * Delete channel history
     *
     * @return  array $discussions
     */
    public function deleteHistoryAction() {
        try {
            $user_id = $this->getUserId();
            $channel_id = $this->request->getQuery('channel_id', null);
            $this->channelService->deleteChannelHistory($user_id, $channel_id);
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
        return parent::successResponse('successfully deleted');
    }

}
