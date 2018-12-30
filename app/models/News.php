<?php

namespace App\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class News extends AccountWithNotDeletedWithCascade
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $news_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $publish_date;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $news_text;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $title;

    const publicColumns = ['news_id', 'publish_date', 'news_text', 'title'];

    const publicColumnsInStr = 'news_id, publish_date, news_text, title';

    /**
     * Method to set the value of field newId
     *
     * @param integer $news_id
     * @return $this
     */
    public function setNewsId($news_id)
    {
        $this->news_id = $news_id;

        return $this;
    }

    /**
     * Method to set the value of field date
     *
     * @param string $publish_date
     * @return $this
     */
    public function setPublishDate($publish_date)
    {
        $this->publish_date = $publish_date;

        return $this;
    }

    /**
     * Method to set the value of field newText
     *
     * @param string $news_text
     * @return $this
     */
    public function setNewsText($news_text)
    {
        $this->news_text = $news_text;

        return $this;
    }

    /**
     * Returns the value of field newId
     *
     * @return integer
     */
    public function getNewsId()
    {
        return $this->news_id;
    }

    /**
     * Returns the value of field date
     *
     * @return string
     */
    public function getPublishDate()
    {
        return $this->publish_date;
    }

    /**
     * Returns the value of field newText
     *
     * @return string
     */
    public function getNewsText()
    {
        return $this->news_text;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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

    public function delete($delete = false, $deletedCascade = false, $data = null, $whiteList = null)
    {
        if ($delete) {
            try {
                // Создаем менеджера транзакций
                $manager = new TxManager();
                // Запрос транзакции
                $transaction = $manager->get();
                $this->setTransaction($transaction);
                $images = ImagesNews::findByNewsId($this->getNewsId());

                foreach ($images as $image) {
                    $image->setTransaction($transaction);
                    if (!$image->delete()) {
                        $transaction->rollback(
                            "Не удалось удалить изображение");
                        foreach ($image->getMessages() as $message) {
                            $this->appendMessage($message->getMessage());
                        }
                        return false;
                    };
                }


                $transaction->commit();
            } catch (TxFailed $e) {
                $message = new Message(
                    $e->getMessage()
                );

                $this->appendMessage($message);
                return false;
            }
        }
        $result = parent::delete($delete, $deletedCascade, $data, $whiteList);

        return $result;
    }

    public static function getNewsForCurrentUser($userId)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $str = "SELECT ";
        foreach (News::publicColumns as $column) {
            $str .= $column . ", ";
        }

        $str .= "account_id";

        $str .= " FROM ((SELECT * FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id)
                    INNER JOIN public.\"favoriteCompanies\" favc ON 
                                (a.company_id = favc.company_id)
                    WHERE favc.user_id = :userId)
                    UNION
                    (SELECT * FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id AND a.company_id is null)
                    INNER JOIN public.\"favoriteUsers\" favu
                    ON (a.user_id = favu.user_object)
                    WHERE favu.user_subject = :userId)) as foo
                    ORDER BY foo.publish_date desc";

        $query = $db->prepare($str);
        $result = $query->execute([
            'userId' => $userId,
        ]);

        $news = $query->fetchAll(\PDO::FETCH_ASSOC);

        return News::handleNewsFromArray($news);
    }

    public static function getAllNewsForCurrentUser($userId)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $str = "SELECT ";
        foreach (News::publicColumns as $column) {
            $str .= $column . ", ";
        }

        $str .= "subjectid, subjecttype";

        $str .= " FROM ((SELECT n.* FROM public.news n INNER JOIN public.\"favoriteCompanies\" favc
                    ON (n.subjectid = favc.companyid AND n.subjecttype = 1)
                    WHERE favc.userid = :userId)
                    UNION
                    (SELECT n.* FROM public.news n INNER JOIN public.\"favoriteUsers\" favu
                    ON (n.subjectid = favu.userobject AND n.subjecttype = 0)
                    WHERE favu.usersubject = :userId)
                    UNION
                    (SELECT * FROM public.news 
                    WHERE subjectid = :userId and subjecttype = 0)
                    ) as foo
                    ORDER BY foo.publishdate desc";

        $query = $db->prepare($str);
        $result = $query->execute([
            'userId' => $userId,
        ]);

        $news = $query->fetchAll(\PDO::FETCH_ASSOC);

        return News::handleNewsFromArray($news);
    }

    public static function getNewsForAccount($accountId)
    {
        $news = News::findByAccount($accountId, 'News.publish_date DESC',
            News::publicColumnsInStr . ', account_id');

        return News::handleNewsFromArray($news);
    }

    private static function handleNewsFromArray(array $news)
    {
        $newsWithAll = [];
        foreach ($news as $newsElement) {
            $newsWithAllElement = $newsElement/*->toArray()*/;
            if($newsElement->getAccountId()!= null) {
                $account = Accounts::findFirstByAccountId($newsElement['account_id']);
                if ($account->getCompanyId() == null) {
                    $user = Userinfo::findUserInfoById($account->getUserId(),Userinfo::shortColumnsInStr);
                    $newsWithAllElement['publisherUser'] = $user;
                } else {
                    $company = Companies::findUserInfoById($account->getCompanyId(),
                        Companies::shortColumnsInStr);
                    $newsWithAllElement['publisherCompany'] = $company;
                }
            }

            $newsWithAllElement['stats'] = new Stats();

            $newsWithAllElement['liked'] = rand() % 2 == 0 ? true : false;

            $imagesNews = ImagesNews::findImagesForNews($newsElement['news_id']);

            $newsWithAllElement['images'] = [];
            foreach ($imagesNews as $image) {
                $newsWithAllElement['images'][] = $image->getImagePath();
            }

            $newsWithAllElement['comments'] = CommentsNews::getComments($newsElement['news_id']);

            $newsWithAllElement['stats']->setComments(count($newsWithAllElement['comments']));
            $newsWithAll[] = $newsWithAllElement;
        }
        return $newsWithAll;
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

            if (!$offer)
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

            $offerWithTask = ['Offer' => $offer, 'Tasks' => $task, 'Userinfo' => $userinfo, 'Tender' => $auction];
            $listNew = ["news" => $new, "offer" => $offerWithTask];


        } else if ($new->getNewType() == 2) {
            $review = Reviews::findFirstByIdReview($new->getIdentify());

            $userId = $review->getUserIdObject();

            $favUsers = Favoriteusers::findByUserObject($userId);

            foreach ($favUsers as $favUser) {
                $userIds[] = $favUser->getUserSubject();
            }

            $userinfo = Userinfo::findFirstByUserId($review->getUserIdSubject());

            $reviewAndUserinfo = ['reviews' => $review, 'Userinfo' => $userinfo];
            $listNew = ["news" => $new, "review" => $reviewAndUserinfo];
        }

        $this->sendPushToUser($new, $userIds, $listNew);
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
