<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

use Emarref\Jwt\Claim;

class Accesstokens extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $tokenid;
    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;
    /**
     *
     * @var string
     * @Column(type="string", length=68, nullable=false)
     */
    protected $token;
    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $lifetime;
    /**
     * Method to set the value of field tokenid
     *
     * @param integer $tokenid
     * @return $this
     */
    public function setTokenid($tokenid)
    {
        $this->tokenid = $tokenid;
        return $this;
    }
    /**
     * Method to set the value of field userid
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;
        return $this;
    }
    /**
     * Method to set the value of field token
     *
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = hash('sha256',$token);
        return $this;
    }
    /**
     * Returns the value of field tokenid
     *
     * @return integer
     */
    public function getTokenid()
    {
        return $this->tokenid;
    }
    public function setLifetime($lifetime = null)
    {
        if($lifetime == null){
            $this->lifetime = date('Y-m-d H:i:s',time() + 604800);
        } else
            $this->lifetime = $lifetime;
        return $this;
    }
    public function getLifetime()
    {
        return $this->lifetime;
    }
    /**
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserid()
    {
        return $this->userid;
    }
    /**
     * Returns the value of field token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();
        $validator->add(
            'userid',
            new Callback(
                [
                    "message" => "Пользователь не существует",
                    "callback" => function ($token) {
                        $user = Users::findFirst(['userid = :userId:','bind' => ['userId' => $token->getUserId()]],
                            false);
                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );
        $validator->add(
            'token',
            new PresenceOf(
                [
                    "message" => "Токен не заполнен",
                ]
            )
        );
        $validator->add(
            'lifetime',
            new PresenceOf(
                [
                    "message" => "Не указано время жизни токена",
                ]
            )
        );
        return $this->validate($validator);
    }
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("accesstokens");
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'accesstokens';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Accesstokens[]|Accesstokens|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }
    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Accesstokens|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        //SupportClass::writeMessageInLogFile('Зашел в функцию findFirst модели accesstokens. Параметры: '.$parameters);
        $result = parent::findFirst($parameters);
        return $result;
    }
    public static function GenerateToken($userId, $login, $role, $lifetime){
        $header = base64_encode('{"alg":"RS512","typ":"JWT"}');
        $payload = base64_encode(json_encode(['userId'=>$userId,'login'=>$login,'role'=>$role,'lifetime'=> $lifetime]));
        $signature = '.';
        //$private = openssl_pkey_get_private(,'foobar');
        $di = Phalcon\DI::getDefault();

        $riv = file_get_contents($di->getConfig()['token_rsa']['pathToPrivateKey']);

        $pk  = openssl_get_privatekey($riv,$di->getConfig()['token_rsa']['password']);

        $err = openssl_error_string();
        $result = openssl_private_encrypt($header.'.'.$payload,$signature,$pk, OPENSSL_PKCS1_PADDING);
        if(!$result){
            return openssl_error_string();
        }

        return $header.'.'.$payload.'.'.base64_encode($signature);
    }

    public static function checkToken($token){
        $data = explode('.',$token);
        //openssl_public_encrypt($header.$payload,$signature,PRIVATE_KEY,OPENSSL_PKCS1_PADDING);
        $di = Phalcon\DI::getDefault();

        $pub = file_get_contents($di->getConfig()['token_rsa']['pathToPublicKey']);

        $pk  = openssl_get_publickey($pub);

        openssl_public_decrypt(base64_decode($data[2]),$signature,$pk,OPENSSL_PKCS1_PADDING);

        if($data[0].'.'.$data[1] == $signature)
            return base64_decode($data[1]);
        else
            return false;
    }
}