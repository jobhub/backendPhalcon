<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\ActivationCodes;

use App\Models\UsersSocial;
use Phalcon\DI\FactoryDefault as DI;

//Models
use App\Models\Users;
use App\Models\Phones;
use App\Models\PasswordResetCodes;

use App\Libs\SupportClass;
use App\Libs\PHPMailerApp;
/**
 * business and other logic for authentication. Maybe just creation simple objects.
 *
 * Class UsersService
 */
class CommonService extends AbstractService
{
    const ADDED_CODE_NUMBER = 35000;

    const ERROR_UNABLE_SEND_ACTIVATION_CODE_TO_SOCIAL = 1 + self::ADDED_CODE_NUMBER;

    public function getIdFromObject($type, $some_object){
        switch ($type) {
            case self::TYPE_USER:
                $id = $some_object->getUserId();
                break;
            case self::TYPE_NEWS:
                $id = $some_object->getNewsId();
                break;
            case self::TYPE_REVIEW:
                $id = $some_object->getReviewId();
                break;
            case self::TYPE_SERVICE:
                $id = $some_object->getServiceId();
                break;
            case self::TYPE_TEMP:
                $id = $some_object->getId();
                break;
            case self::TYPE_RASTRENIYA:
                $id = $some_object->getId();
                break;
            default:
                throw new ServiceException('Invalid type of image', self::ERROR_INVALID_OBJECT_TYPE);
        }
        return $id;
    }
}
