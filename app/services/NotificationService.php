<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\InvitesCompanyManager;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class NotificationService extends AbstractService
{
    const TYPE_INVITE_TO_BE_MANAGER = 1;

    const ADDED_CODE_NUMBER = 33000;

    /** Unable to create user */
    const ERROR_INVALID_NOTIFICATION_TYPE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_SEND_NOTIFICATION = 2 + self::ADDED_CODE_NUMBER;

    public function sendNotification($some_object, $type)
    {
        switch ($type) {
            case self::TYPE_INVITE_TO_BE_MANAGER:
                if($some_object->invited->getEmail()!=null) {
                    $this->sendMail('notifications',
                        'emails/notifications/notify_invite_to_be_manager_in_company',
                        ['company_name' => $some_object->WhereInvited->getName(),
                            'email' => $some_object->InvitedPerson->getEmail()],
                        'Уведомление');
                }
                break;
            default:
                throw new ServiceException('Invalid type of notification',
                    self::ERROR_INVALID_NOTIFICATION_TYPE);
        }
    }
}
