<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class News extends SubjectsWithNotDeleted
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $newid;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $newtext;

    /**
     * Method to set the value of field newId
     *
     * @param integer $newid
     * @return $this
     */
    public function setNewId($newid)
    {
        $this->newid = $newid;

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
     * Method to set the value of field newText
     *
     * @param string $newtext
     * @return $this
     */
    public function setNewText($newtext)
    {
        $this->newtext = $newtext;

        return $this;
    }

    /**
     * Returns the value of field newId
     *
     * @return integer
     */
    public function getNewId()
    {
        return $this->newid;
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
     * Returns the value of field newText
     *
     * @return string
     */
    public function getNewText()
    {
        return $this->newtext;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();



        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("news");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'news';
    }


    public function save($data = null, $whiteList = null)
    {
        $result = parent::save($data, $whiteList);

        /*(if($result) {
            $this->sendPush($this);
        }*/
        return $result;
    }


    private function sendPush($new)
    {

        $userIds = [];

        if ($new->getNewType() == 0) {
            //Тендеры
            $tender = Auctions::findFirstByAuctionId($new->getIdentify());

            $categoryId = $tender->tasks->getCategoryId();

            $favCategories = FavoriteCategories::findByCategoryId($categoryId);

            foreach ($favCategories as $favCategory) {
                $userIds[] = $favCategory->getUserId();
            }

            $userId = $tender->tasks->getUserId();

            $favUsers = Favoriteusers::findByUserObject($userId);

            foreach ($favUsers as $favUser) {

                $exists = false;
                foreach ($userIds as $userId) {
                    if ($userId == $favUser->getUserSubject()) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $userIds[] = $favUser->getUserSubject();
                }
            }

            $user = Userinfo::findFirstByUserId($tender->tasks->getUserId());
            $auctionId = $tender->getAuctionId();

            $offer = Offers::findFirst("userId = '$userId' and auctionId = '$auctionId'");

            if(!$offer)
                $offer = null;

            $auctionAndTask = ['tender' => $tender, 'tasks' => $tender->tasks, 'Userinfo' => $user, 'offer' => $offer];
            $listNew = ["news" => $new, "tender" => $auctionAndTask];

        } else if ($new->getNewType() == 1) {
            //Предложения

            $offer = Offers::findFirstByOfferId($new->getIdentify());

            $userId = $offer->getUserId();

            $favUsers = Favoriteusers::findByUserObject($userId);

            foreach ($favUsers as $favUser) {
                $userIds[] = $favUser->getUserSubject();
            }

            $auction = $offer->Auctions;
            $task = $offer->auctions->tasks;
            $userinfo = $task->Users->userinfo;

            $offerWithTask = ['Offer' => $offer,'Tasks' => $task, 'Userinfo' => $userinfo, 'Tender'=> $auction];
            $listNew = ["news" => $new, "offer" => $offerWithTask];


        } else if($new->getNewType() == 2) {
            $review = Reviews::findFirstByIdReview($new->getIdentify());

            $userId = $review->getUserIdObject();

            $favUsers = Favoriteusers::findByUserObject($userId);

            foreach($favUsers as $favUser){
                $userIds[] = $favUser->getUserSubject();
            }

            $userinfo = Userinfo::findFirstByUserId($review->getUserIdSubject());

            $reviewAndUserinfo = ['reviews' => $review, 'Userinfo' => $userinfo];
            $listNew = ["news" => $new, "review" => $reviewAndUserinfo];
        }

        $this->sendPushToUser($new,$userIds, $listNew);
    }


    private function sendPushToUser($new, $userIds, $newInfo)
    {
        $curl = curl_init();

        $tokens = [];

        foreach ($userIds as $userId) {
            $token = Tokens::findFirstByUserId($userId);

            if ($token) {
                $tokens[] = $token;
            }
        }

        if (count($tokens) > 0 && count($tokens) < 1000) {
            $tokenStr = [];
            foreach ($tokens as $t)
                $tokenStr[] = $t->getToken();

            //$tokenStr = $token->getToken();

            $newInfo['type'] = 'news';

            $fields = array('registration_ids' => $tokenStr/*$tokenStr*/,
                'name' => 'news',
                'body' => 'news body',
                'data' => $newInfo
            );

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
