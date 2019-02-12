<?php

namespace App\Services;

use App\Models\ActivationCodes;

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
class ResetPasswordService extends AbstractService
{
    const ADDED_CODE_NUMBER = 3000;

    const ERROR_UNABLE_TO_CREATE_RESET_PASSWORD_CODE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_RESET_PASSWORD_CODE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_NO_TIME_TO_RESEND = 3 + self::ADDED_CODE_NUMBER;

    //
    const RIGHT_PASSWORD_RESET_CODE = 0;
    const WRONG_PASSWORD_RESET_CODE = 1;
    const RIGHT_DEACTIVATE_PASSWORD_RESET_CODE = 2;


    public function checkResetPasswordCode(Users $user, string $code){
        if ($user->getPhoneId() == null) {
            $resetCode = PasswordResetCodes::findFirst(['user_id = :userId: and reset_code = :resetCode:',
                'bind' => [
                    'userId' => $user->getUserId(),
                    'resetCode' => $code
                ]]);
        } else {
            $resetCode = PasswordResetCodes::findFirst(['user_id = :userId: and reset_code_phone = :resetCode:',
                'bind' => [
                    'userId' => $user->getUserId(),
                    'resetCode' => $code
                ]]);
        }

        if ($resetCode) {
            return self::RIGHT_PASSWORD_RESET_CODE;
        }

        $resetCode = PasswordResetCodes::findFirst(['user_id = :userId: and deactivate_code = :resetCode:',
            'bind' => [
                'userId' => $user->getUserId(),
                'resetCode' => $code
            ]]);

        if ($resetCode) {
            return self::RIGHT_DEACTIVATE_PASSWORD_RESET_CODE;
        }

        return self::WRONG_PASSWORD_RESET_CODE;
    }

    public function deletePasswordResetCode(int $userId){
        try {
            $code = PasswordResetCodes::findFirstByUserId($userId);

            if (!$code) {
                return true;
            }

            if (!$code->delete()) {
                $errors = SupportClass::getArrayWithErrors($code);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to delete reset password code',
                        self::ERROR_UNABLE_DELETE_RESET_PASSWORD_CODE, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to delete reset password code',
                        self::ERROR_UNABLE_DELETE_RESET_PASSWORD_CODE);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function sendPasswordResetCode(Users $user)
    {
        $resetCode = $this->createPasswordResetCode($user);

        if($user->getPhoneId()==null){
            $this->sendMail('reset_code_letter','emails/reset_code_letter',
                ['resetcode' => $resetCode->getResetCode(),
                    'deactivate' => $resetCode->getDeactivateCode(),
                    'email' => $user->getEmail()],'Подтвердите сброс пароля');
        } else {
            //Тут типа отправляем
            $res = true;
            //Отправили
        }
    }

    /**
     * Creating code for reset password.
     * @param Users $user
     * @return PasswordResetCodes
     */
    public function createPasswordResetCode(Users $user)
    {
        $resetCode = PasswordResetCodes::findFirstByUserId($user->getUserId());
        if (!$resetCode) {
            $resetCode = new PasswordResetCodes();
            $resetCode->setUserId($user->getUserId());
        } else if (strtotime($resetCode->getTime()) > strtotime(date('Y-m-d H:i:sO')) - PasswordResetCodes::RESEND_TIME) {
            throw new ServiceExtendedException('Time to resend did\'t come', self::ERROR_NO_TIME_TO_RESEND, null, null,
                ['time_left' => strtotime($resetCode->getTime())
                    - (strtotime(date('Y-m-d H:i:sO')) - PasswordResetCodes::RESEND_TIME)]);
        }

        if ($user->getPhoneId() == null) {
            $resetCode->setResetCode($this->generateResetCode($user->getUserId()));
            $resetCode->setDeactivateCode($this->generateDeactivateResetCode($user->getUserId()));
        } else {
            //Иначе отправляем на телефон
            $resetCode->setResetCodePhone($this->generateResetCodePhone($user->getUserId()));
        }

        $resetCode->setTime(date('Y-m-d H:i:s'));

        if (!$resetCode->save()) {
            $errors = SupportClass::getArrayWithErrors($resetCode);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to create reset password code',
                    self::ERROR_UNABLE_TO_CREATE_RESET_PASSWORD_CODE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to create reset password code',
                    self::ERROR_UNABLE_TO_CREATE_RESET_PASSWORD_CODE);
            }
        }

        return $resetCode;
    }

    public function generateResetCodePhone($userId)
    {
        $hash = hash('sha256',$userId . time() . rand());

        return substr($hash,5,4);
    }

    public function generateResetCode($userId)
    {
        $hash = hash('sha256',$userId . time() . rand());
        return substr($hash,5,4);
    }

    public function generateDeactivateResetCode($userId)
    {
        $hash = hash('sha256',$userId . time() . rand(). '-no');
        return substr($hash,5,4);
    }
}
