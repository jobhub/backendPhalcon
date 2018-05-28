<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class CoordinationAPIController extends Controller
{

    /**
     * Creates a new message
     */
    public function addMessageAction()
    {
        if ($this->request->isPut()) {
            $response = new Response();

            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $auctionId = $this->request->getPut('tenderId');
            $auction = Auctions::findFirstByAuctionId($auctionId);

            $offer = Offers::findFirst("userId = $userId and auctionId = $auctionId");
            if ($userId == $auction->tasks->getUserId()) {
                $input = 0;
            } else if ($offer != null && $offer->getSelected() == 1)
                $input = 1;
            else {
                //Вообще не имеет отношения к этому заданию
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA"
                    ]
                );
                return $response;
            }


            $message = new Messages();
            $message->setAuctionid($auctionId);
            $message->setInput($input);
            $message->setMessage($this->request->getPut("message"));
            $message->setDate(date('Y-m-d H:i:s'));


            if (!$message->save()) {
                foreach ($message->getMessages() as $message) {
                    $this->flash->error($message);
                }

                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA"
                    ]
                );

                return $response;
            }

            $this->sendPush($message);

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function getMessagesAction($auctionId, $date = null)
    {
        if ($this->request->isGet()) {

            $response = new Response();

            //$auctionId = $this->request->getPost('auctionId');

            $task = Auctions::findFirstByAuctionId($auctionId)->tasks;


            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //$auctionId = $this->request->getPut('auctionId');
            $auction = Auctions::findFirstByAuctionId($auctionId);

            $offer = Offers::findFirst("userId = $userId and auctionId = $auctionId");
            if ($userId == $auction->tasks->getUserId()) {
                $input = 0;
            } else if ($offer != null && $offer->getSelected() == 1)
                $input = 1;
            else {
                //Вообще не имеет отношения к этому заданию
                $response->setJsonContent(
                    [
                        "status" => ['status' => "WRONG_DATA"]
                    ]
                );
                return $response;
            }

            if($date == null){
                //Отдаем все сообщения
                $messages = Messages::find("auctionId = $auctionId");

                if(!$messages)
                    $messages = [];

                $response->setJsonContent(
                    [
                        "status" => ['status' => "OK"],
                        "messages" =>$messages
                    ]
                );
                return $response;
            }
            else{
                //Отдаем только после указанной даты
                $messages = Messages::find("auctionId = $auctionId and date < $date");

                if(!$messages)
                    $messages = [];

                $response->setJsonContent(
                    [
                        "status" => ['status' => "OK"],
                        "messages" =>$messages
                    ]
                );
                return $response;
            }

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function selectOfferAction()
    {
        if($this->request->isPost()){
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $auction = Auctions::findFirstByAuctionId($this->request->getPost('auctionId'));

            if($auction->tasks->getUserId() == $userId){
                $offer = Offers::findFirstByOfferId($this->request->getPost('offerId'));


                if($offer->getAuctionId() == $auction->getAuctionId()){
                    $this->db->begin();
                    $auction->tasks->setStatus(1);
                    if(!$auction->tasks->save()){
                        $this->db->rollback();

                        foreach ($auction->tasks->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "errors" => $errors,
                                "status" => "WRONG_DATA"
                            ]);

                        return $response;
                    }

                    $offer->setSelected(1);
                    if(!$offer->save()){
                        $this->db->rollback();

                        foreach ($offer->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "errors" => $errors,
                                "status" => "WRONG_DATA"
                            ]);

                        return $response;
                    }

                    $this->db->commit();

                    $response->setJsonContent(
                        [
                            "status" => "OK"
                        ]);

                    return $response;
                }

                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => ["Что-то пошло не так"]
                    ]
                );

            }
            else{
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => ["Тендер не принадлежит пользователю"]
                    ]
                );
                return $response;
            }

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function addTokenIdAction(){
        if($this->request->isPost()){

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //$this->session->set("push_token_id",$this->responce->getPost("tokenId"));
            $response = new Response();

            /*$tokenstr = $this->request->getPost("tokenId");

            $count = strlen($tokenstr);*/

            $token = Tokens::findFirstByUserId($userId);

            if(!$token) {

                $token = new Tokens();
                $token->setUserId($userId);
            }
            $token->setToken($this->request->getPost("tokenId"));

            if(!$token->save()){
                foreach ($token->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "WRONG_DATA"
                    ]);

                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function clearTokensAction(){
        if($this->request->isPost()){

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $response = new Response();

            $tokens = Tokens::findByUserId($userId);

            foreach($tokens as $token){
                $token->delete();
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function sendPush($message){
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
