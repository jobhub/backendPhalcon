<?php

namespace App\Libs\SocialAuther\Adapter;

class Instagram extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'id',
            'email'      => 'email',
            'name'       => 'name',
            'socialPage' => 'link',
            'avatar'     => 'picture',
            'sex'        => 'gender'
        );

        $this->provider = 'instagram';
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate($code)
    {
        $result = false;

        if (empty($code)) {
            $params = array(
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUri,
                'grant_type'    => 'authorization_code',
                'code'          => $code
            );

            $tokenInfo = $this->post('https://api.instagram.com/oauth/access_token', $params);

            if (isset($tokenInfo['access_token'])) {
                //$params['access_token'] = $tokenInfo['access_token'];

                $params = ['access_token' => $tokenInfo['access_token']];

                $userInfo = $this->get('https://api.instagram.com/v1/users/self', $params);
                if (isset($userInfo[$this->socialFieldsMap['socialId']])) {
                    $this->userInfo = $userInfo;
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
            'auth_url'    => 'https://api.instagram.com/oauth/authorize/',
            'auth_params' => array(
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'client_id'     => $this->clientId,
                'scope'         => 'basic'
            )
        );
    }

    public function getUser(){
        return [
            'network'=>$this->getProvider(),
            'identity'=>$this->getSocialId(),
            'first_name'=>$this->getFirstName(),
            'last_name'=>$this->getLastName(),
            'male'=>$this->getSex(),
            //'country'=>$this->getCountry(),
            //'city'=>$this->getCity(),
            'profile'=>$this->getSocialPage(),
            'email'=>$this->getEmail(),
            //'city_id'=>$this->getCityId(),
            //'status'=>$this->getStatus(),
            //'about'=>$this->getAbout(),
            'birthday'=>$this->getBirthday(),
            'uri_to_photo'=>$this->getAvatar(),
            //'photo_name'=>$this->getPhotoName()
        ];
    }
}