<?php

namespace App\Controllers;

use App\Libs\SimpleULogin;
use App\Libs\SocialAuther\Adapter\Facebook;
use App\Libs\SocialAuther\Adapter\Google;
use App\Libs\SocialAuther\Adapter\Instagram;
use App\Models\Accounts;
use App\Models\UsersSocial;
use App\Services\AbstractService;
use App\Services\AccountService;
use App\Services\CityService;
use App\Services\ImageService;
use App\Services\SettingsService;
use App\Services\SocialNetService;
use App\Services\UserInfoService;
use Phalcon\Http\Client\Exception;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

use App\Libs\SupportClass;

use App\Models\Phones;
use App\Models\Accesstokens;
use App\Models\Users;

use App\Services\UserService;
use App\Services\AuthService;

use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http404Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;

use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use ULogin\Auth;
use App\Libs\SocialAuther\Adapter\Vk;
use App\Libs\SocialAuther\SocialAuther;

/**
 * Class SettingsController
 * Контроллер, предназначеный для работы с настройками пользователя
 */
class SettingsController extends AbstractController
{
    /**
     * Editing current user's settings
     *
     * @access private
     * @method POST
     *
     * @params show_companies
     *
     * @return array
     */
    public function setSettingsAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['show_companies'] = $inputData->show_companies;
        try {
            $userId = self::getUserId();
            $settings = $this->settingsService->getSettingsById($userId);
            $settings = $this->settingsService->changeSettings($settings,$data);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case SettingsService::ERROR_UNABLE_CHANGE_SETTINGS:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case SettingsService::ERROR_SETTINGS_NOT_FOUND:
                    throw new Http422Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Settings were successfully changed',$settings->toArray());
    }
}