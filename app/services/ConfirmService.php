<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\ConfirmationCodes;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;
use App\Models\UsersSocial;


class ConfirmService extends AbstractService
{
    const TYPE_CREATE_COMPANY = 1;

    const ADDED_CODE_NUMBER = 310000;

    const ERROR_UNABLE_TO_CREATE_CONFIRM_CODE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_CONFIRM_CODE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_NO_TIME_TO_RESEND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_SEND_CONFIRM_CODE = 4 + self::ADDED_CODE_NUMBER;

    const RIGHT_CONFIRM_CODE = 0;
    const WRONG_CONFIRM_CODE = 1;
    const RIGHT_DEACTIVATE_CODE = 2;

    public function checkConfirmCode(Users $user, string $code, $type){
        if ($user->getPhoneId() == null) {
            $resetCode = ConfirmationCodes::findFirst(['user_id = :userId: and reset_code = :code: 
                 and type = :type:',
                'bind' => [
                    'userId' => $user->getUserId(),
                    'code' => $code,
                    'type'=>$type
                ]]);
        } else {
            $resetCode = ConfirmationCodes::findFirst(['user_id = :userId: and confirm_code_phone = :code:
            and type = :type:',
                'bind' => [
                    'userId' => $user->getUserId(),
                    'code' => $code,
                    'type'=>$type
                ]]);
        }

        if ($resetCode) {
            return self::RIGHT_CONFIRM_CODE;
        }

        $resetCode = ConfirmationCodes::findFirst(['user_id = :userId: and deactivate_code = :code:
                and type = :type:',
            'bind' => [
                'userId' => $user->getUserId(),
                'resetCode' => $code,
                'type'=>$type
            ]]);

        if ($resetCode) {
            return self::RIGHT_DEACTIVATE_CODE;
        }

        return self::WRONG_CONFIRM_CODE;
    }

    public function deleteConfirmCode(int $userId, $type){
        try {
            $code = ConfirmationCodes::findFirst(['user_id = :userId: and type = :type:',
                'bind'=>['userId'=>$userId, 'type'=>$type]]);

            if (!$code) {
                return true;
            }

            if (!$code->delete()) {
                SupportClass::getErrorsWithException($code,self::ERROR_UNABLE_DELETE_CONFIRM_CODE,
                    'Unable to delete confirm code');
            }

            return true;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function sendConfirmCode(Users $user, $type)
    {
        $code = $this->createConfirmCode($user,$type);

        if($user->getPhoneId()==null){
            $this->sendMail('confirm_code_letter','emails/confirm_code_letter',
                ['code' => $code->getConfirmCodeEmail(),
                    'deactivate' => $code->getDeactivateCode(),
                    'email' => $user->getEmail()],'Подтвердите создание компании');
        } else {
            $this->sendSms($user->phones->getPhone(),$this->getMessageForSmsForConfirmCode($code));
        }
    }

    public function getMessageForSmsForConfirmCode(ConfirmationCodes $code){
        if($code==null){
            throw new ServiceException('Код для подтверждения не должен быть null',self::ERROR_UNABLE_SEND_CONFIRM_CODE);
        }

        return 'Код '.$code->getConfirmCodePhone();
    }

    /**
     * Creating code for reset password.
     * @param Users $user
     * @return ConfirmationCodes
     */
    public function createConfirmCode(Users $user, $type)
    {
        $code = ConfirmationCodes::findFirst(['user_id = :userId: and type = :type:',
            'bind'=>['userId'=>$user->getUserId(), 'type'=>$type]]);
        if (!$code) {
            $code = new ConfirmationCodes();
            $code->setUserId($user->getUserId());
        } else if (strtotime($code->getTime()) > strtotime(date('Y-m-d H:i:sO')) - ConfirmationCodes::RESEND_TIME) {
            throw new ServiceExtendedException('Time to resend did\'t come', self::ERROR_NO_TIME_TO_RESEND, null, null,
                ['time_left' => strtotime($code->getTime())
                    - (strtotime(date('Y-m-d H:i:sO')) - ConfirmationCodes::RESEND_TIME)]);
        }

        if ($user->getPhoneId() == null) {
            $code->setConfirmCodeEmail($this->generateCodeEmail($user->getUserId(), $type));
            $code->setDeactivateCode($this->generateDeactivateCode($user->getUserId(), $type));
        } else {
            //Иначе отправляем на телефон
            $code->setConfirmCodePhone($this->generateCodePhone($user->getUserId(), $type));
        }

        $code->setTime(date('Y-m-d H:i:sO'));
        $code->setType($type);

        if (!$code->save()) {
            $errors = SupportClass::getArrayWithErrors($code);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to create confirm code',
                    self::ERROR_UNABLE_TO_CREATE_CONFIRM_CODE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to create confirm code',
                    self::ERROR_UNABLE_TO_CREATE_CONFIRM_CODE);
            }
        }

        return $code;
    }

    public function generateCodePhone($userId, $type)
    {
        $hash = hash('sha256',$userId . time() . rand() . $type);

        return /*substr($hash,5,4)*/rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }

    public function generateCodeEmail($userId, $type)
    {
        $hash = hash('sha256',$userId . time() . rand() . $type);
        return /*substr($hash,5,4)*/rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }

    public function generateDeactivateCode($userId, $type)
    {
        $hash = hash('sha256',$userId . time() . rand(). '-no' . $type);
        return /*substr($hash,5,4)*/rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }
}
