<?php

class Messages extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $messageId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $auctionId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $input;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $message;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date;

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
     * Method to set the value of field auctionId
     *
     * @param integer $auctionId
     * @return $this
     */
    public function setAuctionId($auctionId)
    {
        $this->auctionId = $auctionId;

        return $this;
    }

    /**
     * Method to set the value of field input
     *
     * @param integer $input
     * @return $this
     */
    public function setInput($input)
    {
        $this->input = $input;

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
     * Returns the value of field messageId
     *
     * @return integer
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * Returns the value of field auctionId
     *
     * @return integer
     */
    public function getAuctionId()
    {
        return $this->auctionId;
    }

    /**
     * Returns the value of field input
     *
     * @return integer
     */
    public function getInput()
    {
        return $this->input;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("service_services");
        $this->setSource("messages");
        $this->belongsTo('auctionId', '\Auctions', 'auctionId', ['alias' => 'Auctions']);
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


    public function save($data = null, $whiteList = null)
    {
        $result = parent::save($data, $whiteList);

        if($result) {
            $this->sendPush($this);
        }
        return $result;
    }

    private function sendPush($message){
        $auction = Auctions::findFirstByAuctionId($message->getAuctionId());
        $auctionId = $message->getAuctionId();

        $offer = Offers::findFirst("auctionId = $auctionId and selected = true");

        $this->sendPushToUser($message,$auction->tasks->getUserId());

        $this->sendPushToUser($message,$offer->getUserId());
    }

    private function sendPushToUser($message, $userId){
        $curl = curl_init();

        $token = Tokens::findFirstByUserId($userId);

        if($token) {

            $tokenStr = $token->getToken();

            $messageText = $message->getMessage();
            $date = $message->getDate();
            $auction = $message->getAuctionId();
            $input = $message->getInput();
            $messageId = $message->getMessageId();

            $fields = array('to' => $tokenStr,
                'data' => array(
                    'message' => $messageText,
                    'date' => $date,
                    'auctionId' => $auction,
                    'input' => $input,
                    'messageId' => $messageId
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
