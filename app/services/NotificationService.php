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
    const TYPE_COMPANY_INVITE_TO_REGISTER_AND_BE_MANAGER = 2;

    const ADDED_CODE_NUMBER = 33000;

    /** Unable to create user */
    const ERROR_INVALID_NOTIFICATION_TYPE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_SEND_NOTIFICATION = 2 + self::ADDED_CODE_NUMBER;

    public function sendNotification($some_object, $type, $data = null)
    {
        switch ($type) {
            case self::TYPE_INVITE_TO_BE_MANAGER:
                if ($some_object->invitedPerson->getEmail() != null) {
                    $this->sendMail('notifications',
                        'emails/notifications/notify_invite_to_be_manager_in_company',
                        ['company_name' => $some_object->WhereInvited->getName(),
                            'email' => $some_object->InvitedPerson->getEmail()],
                        'Уведомление');
                }
                break;
            case self::TYPE_COMPANY_INVITE_TO_REGISTER_AND_BE_MANAGER:
                $this->sendMail('notifications',
                    'emails/notifications/notify_company_invite_to_register_and_be_manager',
                    [
                        'company_name' => $some_object->getName(),
                        'email' => $data['email']
                    ],
                    'Уведомление');
                break;
            default:
                throw new ServiceException('Invalid type of notification',
                    self::ERROR_INVALID_NOTIFICATION_TYPE);
        }
    }
}
