<?php

namespace App\Libs\SocialAuther\Adapter;

use App\Libs\SupportClass;

class Vk extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'uid',
            'email'      => 'email',
            'avatar'     => 'photo_big',
            'birthday'   => 'bdate'
        );

        $this->provider = 'vk';
    }

    /**
     * Get user name or null if it is not set
     *
     * @return string|null
     */
    public function getName()
    {
        $result = null;

        if (isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['first_name'] . ' ' . $this->userInfo['last_name'];
        } elseif (isset($this->userInfo['first_name']) && !isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['first_name'];
        } elseif (!isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['last_name'];
        }

        return $result;
    }

    public function getFirstName()
    {
        $result = null;

        if (isset($this->userInfo['first_name'])) {
            $result = $this->userInfo['first_name'];
        }

        return $result;
    }

    public function getLastName()
    {
        $result = null;

        if (isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['last_name'];
        }

        return $result;
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;

        if (isset($this->userInfo['screen_name'])) {
            $result = 'http://vk.com/' . $this->userInfo['screen_name'];
        }

        return $result;
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        $result = null;
        if (isset($this->userInfo['sex'])) {
            $result = $this->userInfo['sex'] == 1 ? 'female' : 'male';
        }

        return $result;
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate($code)
    {
        $result = false;
        SupportClass::writeMessageInLogFile("Вызвал authenticate");

        if (!empty($code)) {
            $params = array(
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'scope'=>'email',
                'v' => 5.92
            );

            SupportClass::writeMessageInLogFile("Перед получением токена");

            $tokenInfo = $this->get('https://oauth.vk.com/access_token', $params);
            $strTokenInfo = var_export($tokenInfo,true);
            SupportClass::writeMessageInLogFile("Получил токен. tokenInfo - ".$strTokenInfo);

            if (isset($tokenInfo['access_token'])) {
                $params = array(
                    'uids'         => $tokenInfo['user_id'],
                    'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big,email,city,country',
                    'access_token' => $tokenInfo['access_token'],
                    'scope'=>'email',
                    'v' => 5.92
                );

                SupportClass::writeMessageInLogFile("Перед запросом данных о юзере");

                $userInfo = $this->get('https://api.vk.com/method/users.get', $params);

                $strUserInfo = var_export($userInfo,true);
                SupportClass::writeMessageInLogFile("Инфа о юзере - ".$strUserInfo);

                if (isset($userInfo['response'][0]['uid'])) {
                    $this->userInfo = $userInfo['response'][0];
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    public function prepareAuthParams()
    {
        return array(
            'auth_url'    => 'http://oauth.vk.com/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'scope'         => 'notify',
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code'
            )
        );
    }

    public function getUser(){
        return [
            's'
        ]
    }
}