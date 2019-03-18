<?php

namespace App\Services;

use App\Libs\ImageLoader;
use App\Models\Accounts;
use App\Models\FavoriteUsers;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\FavoriteCompanies;
use App\Models\Settings;
use Phalcon\Http\Client\Exception;

use Phalcon\Http\Request\File as PhalconFile;

/**
 * business logic for users
 *
 * Class UsersService
 */
class SettingsService extends AbstractService
{

    const ADDED_CODE_NUMBER = 38000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_SETTINGS = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_SETTINGS_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_SETTINGS = 3 + self::ADDED_CODE_NUMBER;

    public function changeSettings(Settings $settings, array $settingsData)
    {
        try {
            $this->fillSettings($settings, $settingsData);
            if ($settings->update() == false) {
                $errors = SupportClass::getArrayWithErrors($settings);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable change settings for user',
                        self::ERROR_UNABLE_CHANGE_SETTINGS, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable change settings for user',
                        self::ERROR_UNABLE_CHANGE_SETTINGS);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $settings;
    }

    private function fillSettings(Settings $settings, array $data)
    {
        if (!empty(trim($data['notification_email'])))
            $settings->setNotificationEmail($data['notification_email']);
        if (!empty(trim($data['notification_sms'])))
            $settings->setNotificationSms($data['notification_sms']);
        if (isset($data['notification_push']))
            $settings->setNotificationPush($data['notification_push']);
        if (isset($data['show_companies']))
            $settings->setShowCompanies($data['show_companies']);
    }

    public function CreateSettings(int $userId)
    {
        try {
            $setting = new Settings();
            $setting->setUserId($userId);

            if ($setting->create() == false) {
                $errors = SupportClass::getArrayWithErrors($setting);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable create settings',
                        self::ERROR_UNABLE_CREATE_SETTINGS, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable create settings',
                        self::ERROR_UNABLE_CREATE_SETTINGS);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $setting;
    }

    public function getSettingsById(int $userId)
    {
        try {
            $settings = Settings::findFirstByUserId($userId);

            if (!$settings || $settings == null) {
                throw new ServiceException('Settings don\'t exists', self::ERROR_SETTINGS_NOT_FOUND);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $settings;
    }
}
