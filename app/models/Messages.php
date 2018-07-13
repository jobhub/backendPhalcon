<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class Messages extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $messageId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $message;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $date;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userIdObject;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userIdSubject;

    /**
     * Method to set the value of field messageId
     *
     * @param integer $messageId
     * @return $this
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * Method to set the value of field message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Method to set the value of field date
     *
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Method to set the value of field userIdObject
     *
     * @param integer $userIdObject
     * @return $this
     */
    public function setUserIdObject($userIdObject)
    {
        $this->userIdObject = $userIdObject;

        return $this;
    }

    /**
     * Method to set the value of field userIdSubject
     *
     * @param integer $userIdSubject
     * @return $this
     */
    public function setUserIdSubject($userIdSubject)
    {
        $this->userIdSubject = $userIdSubject;

        return $this;
    }

    /**
     * Returns the value of field messageId
     *
     * @return integer
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * Returns the value of field message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the value of field date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Returns the value of field userIdObject
     *
     * @return integer
     */
    public function getUserIdObject()
    {
        return $this->userIdObject;
    }

    /**
     * Returns the value of field userIdSubject
     *
     * @return integer
     */
    public function getUserIdSubject()
    {
        return $this->userIdSubject;
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
            'userIdObject',
            new Callback(
                [
                    "message" => "Неверно указано id отправителя",
                    "callback" => function($message) {
                        $user = Users::findFirstByUserId($message->getUserIdObject());

                        if($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'userIdSubject',
            new Callback(
                [
                    "message" => "Неверно указано id получателя",
                    "callback" => function($message) {
                        $user = Users::findFirstByUserId($message->getUserIdSubject());

                        if($user)
                            return true;
                        return false;
                    }
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
        //$this->setSchema("public");
        $this->setSource("messages");
        $this->belongsTo('userIdObject', '\Users', 'userId', ['alias' => 'Users']);
        $this->belongsTo('userIdSubject', '\Users', 'userId', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'messages';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Messages[]|Messages|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Messages|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }


    private function sendPush($message){
        $auction = Auctions::findFirstByAuctionId($message->getAuctionId());
        $auctionId = $message->getAuctionId();

        $offer = Offers::findFirst("auctionId = $auctionId and selected = true");

        $this->sendPushToUser($message,$auction->tasks->getUserId(),$offer->getUserId());

        $this->sendPushToUser($message,$offer->getUserId(),$auction->tasks->getUserId());
    }

    private function sendPushToUser($message, $userId, $otherUserId){
        $curl = curl_init();

        //$token = Tokens::findByUserId($userId);
        $token = Tokens::findFirstByUserId($userId);
        $userinfo = Userinfo::findFirstByUserId($otherUserId);

        if($token) {
            /*$tokenStr = [];
            foreach ($token as $t)
                $tokenStr[] = $t->getToken();*/

            $tokenStr = $token->getToken();

            $messageText = $message->getMessage();
            $date = $message->getDate();
            $auction = $message->getAuctionId();
            $input = $message->getInput();
            $messageId = $message->getMessageId();

            $fields = array('to' => /*json_encode($tokenStr)*/$tokenStr,
                'body' => $userinfo->getFirstname() . $userinfo->getLastname() . ": " . $messageText,
                'data' => array(
                    'message' => $messageText,
                    'date' => $date,
                    'auctionId' => $auction,
                    'input' => $input,
                    'messageId' => $messageId,
                    'type' => 'message'
                ));

            $fields = json_encode($fields);
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://fcm.googleapis.com/fcm/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $fields,
                CURLOPT_HTTPHEADER => array(
                    "Cache-Control: no-cache",
                    "Content-Type: application/json",
                    "Authorization: key=AAAASAGah7I:APA91bHZCCENZwnetcwZmSz3oI0WOU0gOwefoB9Mvx-zZ23HQLfIXg3dx9829rcl0MyJpCdTiRebPg2HxQfvA60p-U209ufvQoJI4-3W_YahmXrJHw5dPiiJ_rfVpw_ku6ZxNNWv-L3V"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
        }
    }

}
