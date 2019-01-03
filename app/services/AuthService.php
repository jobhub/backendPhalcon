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
class AuthService extends AbstractService
{
    const LOGIN_EXISTS = 1;
    const LOGIN_DO_NOT_EXISTS = 0;
    const LOGIN_INCORRECT = 2;

    const ADDED_CODE_NUMBER = 2000;

    const ERROR_USER_DO_NOT_EXISTS = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_USER_ALREADY_ACTIVATED = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_SEND_TO_MAIL = 4 + self::ADDED_CODE_NUMBER;
    /*Time to resend did't come. Return time to resend*/
    const ERROR_NO_TIME_TO_RESEND = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_TO_CREATE_RESET_PASSWORD_CODE = 6 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_RESET_PASSWORD_CODE = 7 + self::ADDED_CODE_NUMBER;
    const ERROR_INCORRECT_PASSWORD = 8 + self::ADDED_CODE_NUMBER;

    //
    const RIGHT_PASSWORD_RESET_CODE = 0;
    const WRONG_PASSWORD_RESET_CODE = 1;
    const RIGHT_DEACTIVATE_PASSWORD_RESET_CODE = 2;

    /**
     * Check login.
     *
     * @param string $login
     * @return int
     */
    public function checkLogin(string $login)
    {
        $result = Phones::isValidPhone($login);

        if (!$result) {
            if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
                return self::LOGIN_INCORRECT;
            }
        }

        $user = Users::findByLogin($login);

        if ($user != false) {
            return self::LOGIN_EXISTS;
        }

        return self::LOGIN_DO_NOT_EXISTS;
    }

    /**
     * Check password for user.
     *
     * @param Users $user
     * @param string $password
     * @return bool
     */
    public function checkPassword(Users $user, string $password)
    {
        $res = $this->security->checkHash($password, $user->getPassword());
        // Формируем ответ
        if (!($user && $res))
            throw new ServiceException('Incorrect password or login', self::ERROR_INCORRECT_PASSWORD);

        return true;
    }

    /**
     * Отправляет активационный код пользователю на почту.
     * @param $user - объект типа User
     * @return boolean.
     */
    public function sendActivationCode(Users $user)
    {
        if (!$user || $user == null) {
            throw new ServiceException('User don\'t exists', self::ERROR_USER_DO_NOT_EXISTS);
        }

        if ($user->getActivated()) {
            throw new ServiceException('User already active', self::ERROR_USER_ALREADY_ACTIVATED);
        }

        if ($user->getEmail() != null) {

            $result = $this->createActivationCode($user);

            //Отправляем письмо.
            $this->sendMailForActivation($result, $user->getEmail());
            return true;
        }

        throw new ServiceException('Активация через sms пока не предусмотрена', 0);
    }

    /**
     * create activation code for user if it not exists. In any case rewrite it with new time and code.
     * @param Users $user
     * @return ActivationCodes. If all ok return ActivationCodes object.
     */
    public function createActivationCode(Users $user)
    {
        $activationCode = ActivationCodes::findFirstByUserid($user->getUserId());

        if (!$activationCode) {
            $activationCode = new ActivationCodes();
            $activationCode->setUserId($user->getUserId());
        } else {
            if (strtotime($activationCode->getTime()) > strtotime(date('Y-m-d H:i:s') . '+00') - ActivationCodes::RESEND_TIME) {
                throw new ServiceExtendedException('Time to resend did\'t come', self::ERROR_NO_TIME_TO_RESEND, null, null,
                    ['time_left' => strtotime($activationCode->getTime())
                        - (strtotime(date('Y-m-d H:i:s' . '+00')) - ActivationCodes::RESEND_TIME)]);
            }
        }

        $activationCode->setActivation($this->generateActivation($user));
        $activationCode->setDeactivation($this->generateDeactivation($user));
        $activationCode->setTime(date('Y-m-d H:i:s'));

        if (!$activationCode->save()) {
            $errors = SupportClass::getArrayWithErrors($activationCode);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to create activation code',
                    self::ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to create activation code',
                    self::ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE);
            }
        }

        return $activationCode;
    }

    /**
     * generate code for activation user account
     * @param Users $user
     * @return string
     */
    public function generateActivation(Users $user)
    {
        $hash = hash('sha256', ($user->getEmail() == null ? ' ' : $user->getEmail()) .
            time() . ($user->getPhoneId() == null ? ' ' : $user->phones->getPhone())
            . $user->getPassword());
        return $hash[12] . $hash[7] . $hash[9] . $hash[53];
    }

    /**
     * generate code for deactivation user account
     * @param Users $user
     * @return string
     */
    public function generateDeactivation(Users $user)
    {
        $hash = hash('sha256', ($user->getEmail() == null ? ' ' : $user->getEmail()) .
            time() . ($user->getPhoneId() == null ? ' ' : $user->phones->getPhone())
            . $user->getPassword() . '-no');
        return $hash[12] . $hash[7] . $hash[9] . $hash[53];
    }

    public function sendMailForActivation(ActivationCodes $activationCode, string $email)
    {
        $this->sendMail('hello_world', 'emails/hello_world',
            ['activation' => $activationCode->getActivation(),
                'deactivation' => $activationCode->getDeactivation(),
                'email' => $email], 'Подтвердите регистрацию в нашем замечательном сервисе');
    }

    public function _registerSession($user)
    {
        $di = DI::getDefault();

        $di->getSession()->set(
            "auth",
            [
                "id" => $user->getUserId(),
                "email" => $user->getEmail(),
                "role" => $user->getRole()
            ]
        );
    }

    public function _registerSessionByData($data)
    {
        $di = DI::getDefault();

        $di->getSession()->set(
            "auth",
            [
                "id" => $data['userId'],
                "login" => $data['login'],
                "role" => $data['role']
            ]
        );
    }

    public function createSession(Users $user)
    {
        SupportClass::writeMessageInLogFile('Начало создания сессии для юзера ' . $user->getEmail() != null ? $user->getEmail() : $user->phones->getPhone());

        $lifetime = date('Y-m-d H:i:s', time() + 604800);
        $token = self::GenerateToken($user->getUserId(), ($user->getEmail() != null ? $user->getEmail() : $user->phones->getPhone()),
            $user->getRole(), $lifetime);

        SupportClass::writeMessageInLogFile('ID юзера при этом - ' . $user->getUserId());

        $this->_registerSession($user);

        return
            [
                'token' => $token,
                'lifetime' => $lifetime
            ];
    }

    public function GenerateToken($userId, $login, $role, $lifetime)
    {
        $header = base64_encode('{"alg":"RS512","typ":"JWT"}');
        $payload = base64_encode(json_encode(['userId' => $userId, 'login' => $login, 'role' => $role, 'lifetime' => $lifetime]));
        $signature = '.';
        //$private = openssl_pkey_get_private(,'foobar');
        $di = DI::getDefault();

        $riv = file_get_contents($di->getConfig()['token_rsa']['pathToPrivateKey']);

        $pk = openssl_get_privatekey($riv, $di->getConfig()['token_rsa']['password']);

        $err = openssl_error_string();
        $result = openssl_private_encrypt($header . '.' . $payload, $signature, $pk, OPENSSL_PKCS1_PADDING);
        if (!$result) {
            return openssl_error_string();
        }

        return $header . '.' . $payload . '.' . base64_encode($signature);
    }

    public function checkToken($token)
    {
        $data = explode('.', $token);
        //openssl_public_encrypt($header.$payload,$signature,PRIVATE_KEY,OPENSSL_PKCS1_PADDING);
        $di = DI::getDefault();

        $pub = file_get_contents($di->getConfig()['token_rsa']['pathToPublicKey']);

        $pk = openssl_get_publickey($pub);

        openssl_public_decrypt(base64_decode($data[2]), $signature, $pk, OPENSSL_PKCS1_PADDING);

        if ($data[0] . '.' . $data[1] == $signature)
            return base64_decode($data[1]);
        else
            return false;
    }


    //Сессия

    /**
     * @return Response
     */
    /*public function destroySession()
    {
        $response = new Response();
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        $tokenRecieved = SecurityPlugin::getTokenFromHeader();
        $token = Accesstokens::findFirst(['userid = :userId: AND token = :token:',
            'bind' => ['userId' => $userId,
                'token' => sha1($tokenRecieved)]]);

        if ($token) {
            $token->delete();
        }

        $this->session->remove('auth');
        $this->session->destroy();
        $response->setJsonContent(
            [
                "status" => STATUS_OK
            ]
        );

        return $response;
    }*/
}
