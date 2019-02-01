<?php

namespace App\Models;

use App\Libs\SupportClass;
use Phalcon\DI\FactoryDefault as DI;

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

    protected $likes;

    protected $news_type;

    const publicColumns = ['news_id', 'publish_date', 'news_text', 'title', 'likes', 'news_type', 'account_id'];

    const publicColumnsInStr = 'news_id, publish_date, news_text, title, likes, news_type, account_id';

    const DEFAULT_RESULT_PER_PAGE = 10;

    /**
     * @return mixed
     */
    public function getNewsType()
    {
        return $this->news_type;
    }

    /**
     * @param mixed $news_type
     */
    public function setNewsType($news_type)
    {
        $this->news_type = $news_type;
    }

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
     * @return mixed
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param mixed $likes
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
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

    public static function findNewsForCurrentUser($userId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $db = DI::getDefault()->getDb();

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        $str = "SELECT  foo.date, foo.data, foo.relname, foo.object_id
	FROM (
        (SELECT row_to_json(n.*) as data, 'news' as relname, n.publish_date as date, n.news_id as object_id 
         FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id)
                    INNER JOIN public.favorite_companies favc ON 
                                (a.company_id = favc.object_id)
                    WHERE favc.subject_id = :userId)
        UNION ALL
        (
    select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
    inner join public.accounts a ON (m.account_id = a.id)
    INNER JOIN public.favorite_companies favc ON (a.company_id = favc.object_id)
                where favc.subject_id = :userId)
      	UNION ALL
        (
            SELECT row_to_json(n.*) as data, 'news' as relname, n.publish_date as date, n.news_id as object_id
                     FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id AND a.company_id is null)
                    INNER JOIN public.favorite_users favu ON (a.user_id = favu.object_id)
                    WHERE favu.subject_id = :userId
        )
        UNION ALL
        (
            select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
                    from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
            inner join public.accounts a ON (m.account_id = a.id and a.company_id is null)
            INNER JOIN public.favorite_users favu ON (a.user_id = favu.object_id)
                        WHERE favu.subject_id = :userId
        )
    ) as foo
                    ORDER BY foo.date desc
                    LIMIT :limit 
                    OFFSET :offset";

        $query = $db->prepare($str);
        $result = $query->execute([
            'userId' => $userId,
            'limit' => $page_size,
            'offset' => $offset
        ]);

        $news = $query->fetchAll(\PDO::FETCH_ASSOC);
        return News::handleNewsSetWithForwards($news);
    }

    public static function findAllNewsForCurrentUser($userId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $db = DI::getDefault()->getDb();

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        $str = "SELECT  foo.date, foo.data, foo.relname, foo.object_id
	FROM (
        (SELECT row_to_json(n.*) as data, 'news' as relname, n.publish_date as date, n.news_id as object_id 
         FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id)
                    INNER JOIN public.favorite_companies favc ON 
                                (a.company_id = favc.object_id)
                    WHERE favc.subject_id = :userId)
        UNION ALL
        (
    select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
    inner join public.accounts a ON (m.account_id = a.id)
    INNER JOIN public.favorite_companies favc ON (a.company_id = favc.object_id)
                where favc.subject_id = :userId)
      	UNION ALL
        (
            SELECT row_to_json(n.*) as data, 'news' as relname, n.publish_date as date, n.news_id as object_id
                     FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id AND a.company_id is null)
                    INNER JOIN public.favorite_users favu ON (a.user_id = favu.object_id)
                    WHERE favu.subject_id = :userId
        )
        UNION ALL
        (
            select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
                    from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
            inner join public.accounts a ON (m.account_id = a.id and a.company_id is null)
            INNER JOIN public.favorite_users favu ON (a.user_id = favu.object_id)
                        WHERE favu.subject_id = :userId
        )
        UNION ALL
        (select row_to_json(n.*) as data, 'news' as relname, n.publish_date as date, n.news_id as object_id
                    from public.news n inner join 
		            public.accounts a ON (n.account_id = a.id and a.company_id is null)
                    where a.user_id = :userId and n.deleted = false and n.publish_date < CURRENT_TIMESTAMP  )
                UNION ALL 
                (
                    select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
                    from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
                    inner join public.accounts a ON (m.account_id = a.id and a.company_id is null)
                        where a.user_id = :userId)
    ) as foo
                    ORDER BY foo.date desc
                    LIMIT :limit 
                    OFFSET :offset";

        $query = $db->prepare($str);
        $result = $query->execute([
            'userId' => $userId,
            'limit' => $page_size,
            'offset' => $offset
        ]);

        $news = $query->fetchAll(\PDO::FETCH_ASSOC);

        return News::handleNewsSetWithForwards($news);
    }

    /*public static function findNewsByAccount($accountId)
    {
        $news = News::findByAccount($accountId, 'News.publish_date DESC',
            News::publicColumnsInStr . ', account_id');

        return News::handleNewsFromArray($news);
    }*/

    public static function findNewsByCompany($companyId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $db = DI::getDefault()->getDb();

        $sql = 'Select foo.date, foo.data, foo.relname, foo.object_id from (
                (  select row_to_json(n.*) as data, \'news\' as relname, n.publish_date as date, n.news_id as object_id
                    from public.news n inner join 
		            public.accounts a ON (n.account_id = a.id)
                    where a.company_id = :companyId and n.deleted = false and n.publish_date < CURRENT_TIMESTAMP  )
                UNION ALL (
                    select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
                    from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
                    inner join public.accounts a ON (m.account_id = a.id)
                        where a.company_id = :companyId)
                ) foo
                order by foo.date desc
                LIMIT :limit
                OFFSET :offset';

        $query = $db->prepare($sql);
        $result = $query->execute([
            'companyId' => $companyId,
            'limit' => $page_size,
            'offset' => $offset,
        ]);

        $news = $query->fetchAll(\PDO::FETCH_ASSOC);

        return self::handleNewsSetWithForwards($news);
    }

    public static function findNewsByUser($userId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $db = DI::getDefault()->getDb();

        $sql = 'Select foo.date, foo.data, foo.relname, foo.object_id from (
                (  select row_to_json(n.*) as data, \'news\' as relname, n.publish_date as date, n.news_id as object_id
                    from public.news n inner join 
		            public.accounts a ON (n.account_id = a.id and a.company_id is null)
                    where a.user_id = :userId and n.deleted = false and n.publish_date < CURRENT_TIMESTAMP  )
                UNION ALL (
                    select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
                    from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
                    inner join public.accounts a ON (m.account_id = a.id and a.company_id is null)
                        where a.user_id = :userId)
                ) foo
                order by foo.date desc
                LIMIT :limit
                OFFSET :offset';

        $query = $db->prepare($sql);
        $query->execute([
            'userId' => $userId,
            'limit' => $page_size,
            'offset' => $offset,
        ]);

        $newsResultset = $query->fetchAll(\PDO::FETCH_ASSOC);
        $newsResult = self::handleNewsSetWithForwards($newsResultset);
        /*foreach ($newsResultset as $newsElement){
            $resultElement = [];
            switch ($newsElement['relname']){
                case 'news':{
                    $news = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                    $news = News::getPublicInfoFromArray($news);

                    //$news = News::findNewsById($newsElement['object_id'],self::publicColumns)->toArray();
                    $resultElement = self::handleNewsFromArray([$news])[0];

                    $resultElement['is_forward'] = false;

                    $newsResult[] = $resultElement;

                    break;
                }
                case 'forwards_services':{
                    $service = Services::findServiceById($newsElement['object_id'])->toArray();

                    if(is_null($service))
                        break;

                    $resultElement['service'] = Services::handleServiceForNews($service);

                    $forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                    $resultElement = self::addForwardData($forwardData,$resultElement);

                    $newsResult[] = $resultElement;

                    break;
                }
                case 'forwards_news':{
                    $news = News::findNewsById($newsElement['object_id'],News::publicColumns)->toArray();

                    if(is_null($news))
                        break;

                    $resultElement['news'] = News::handleNewsFromArray([$news])[0];

                    $forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                    $resultElement = self::addForwardData($forwardData,$resultElement);

                    $newsResult[] = $resultElement;

                    break;
                }
                case 'forwards_images_users':{
                $imageUser = ImagesUsers::findImageById($newsElement['object_id'])->toArray();

                if(is_null($imageUser))
                    break;

                $resultElement['image_user'] = ImagesUsers::handleImages([$imageUser])[0];

                $forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                $resultElement = self::addForwardData($forwardData,$resultElement);

                $newsResult[] = $resultElement;

                break;
                }
            }
        }*/

        return /*self::handleNewsFromArray($news);*/
            $newsResult;
    }

    /**
     * @param array $newsResultset - must have fields [date, data, relname, object_id]
     *
     * @return array with news and all forwards for user
     */
    public static function handleNewsSetWithForwards(array $newsResultset)
    {
        $newsResult = [];
        foreach ($newsResultset as $newsElement) {
            $resultElement = [];
            switch ($newsElement['relname']) {
                case 'news': {
                    $news = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                    $news = News::getPublicInfoFromArray($news);

                    //$news = News::findNewsById($newsElement['object_id'],self::publicColumns)->toArray();
                    $resultElement = self::handleNewsFromArray([$news])[0];

                    $resultElement['is_forward'] = false;

                    $newsResult[] = $resultElement;

                    break;
                }
                case 'forwards_services': {
                    $service = Services::findServiceById($newsElement['object_id'])->toArray();

                    if (is_null($service))
                        break;

                    $resultElement['service'] = Services::handleServiceForNews($service);

                    $forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                    $resultElement = self::addForwardData($forwardData, $resultElement);

                    $newsResult[] = $resultElement;

                    break;
                }
                case 'forwards_news': {
                    $news = News::findNewsById($newsElement['object_id'], News::publicColumns)->toArray();

                    if (is_null($news))
                        break;

                    $resultElement['news'] = News::handleNewsFromArray([$news])[0];

                    $forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                    $resultElement = self::addForwardData($forwardData, $resultElement);

                    $newsResult[] = $resultElement;

                    break;
                }
                case 'forwards_images_users': {
                    $imageUser = ImagesUsers::findImageById($newsElement['object_id'])->toArray();

                    if (is_null($imageUser))
                        break;

                    $resultElement['image_user'] = ImagesUsers::handleImages([$imageUser])[0];

                    $forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                    $resultElement = self::addForwardData($forwardData, $resultElement);

                    $newsResult[] = $resultElement;

                    break;
                }
            }
        }
        return $newsResult;
    }

    public static function addForwardData(array $data, array $resultElement)
    {
        $resultElement['forward_text'] = $data['forward_text'];
        $resultElement['forward_date'] = $data['forward_date'];
        $resultElement['is_forward'] = true;

        $account = Accounts::findFirstById($data['account_id']);

        if ($account != null) {
            if($account->getCompanyId()==null)
                $resultElement['publisher_user'] = $account->getUserInfomations();
            else
                $resultElement['publisher_company'] = $account->getUserInfomations();
        }

        return $resultElement;
    }

    private static function handleNewsFromArray(array $news, $accountId = null)
    {
        $newsWithAll = [];

        if ($accountId == null) {
            $session = DI::getDefault()->get('session');
            $accountId = $session->get('accountId');
        }

        foreach ($news as $newsElement) {
            $newsWithAllElement = $newsElement;
            unset($newsWithAllElement['likes']);
            if ($newsElement['account_id'] != null) {
                $account = Accounts::findFirstById($newsElement['account_id']);
                if ($account->getCompanyId() == null) {
                    $user = Userinfo::findUserInfoById($account->getUserId(), Userinfo::shortColumns);
                    $newsWithAllElement['publisherUser'] = $user;
                } else {
                    $company = Companies::findUserInfoById($account->getCompanyId(),
                        Companies::shortColumns);
                    $newsWithAllElement['publisherCompany'] = $company;
                }
            }

            $imagesNews = ImagesNews::findImagesForNews($newsElement['news_id']);

            $newsWithAllElement['images'] = [];
            foreach ($imagesNews as $image) {
                $newsWithAllElement['images'][] = $image['image_path'];
            }

            $last_comment = CommentsNews::findLastParentComment('App\Models\CommentsNews', $newsElement['news_id']);

            $newsWithAllElement['last_comment'] = $last_comment;

            //$newsWithAllElement['stats']['comments'] = count(CommentsNews::findByObjectId($newsElement['news_id']));
            $newsWithAllElement = LikeModel::handleObjectWithLikes($newsWithAllElement, $newsElement, $accountId);
            $newsWithAllElement = ForwardsInNewsModel::handleObjectWithForwards('App\Models\ForwardsNews',$newsWithAllElement, $newsElement['news_id'], $accountId);

            $newsWithAllElement['stats']['comments'] = CommentsModel::getCountOfComments('comments_news', $newsElement['news_id']);

            $newsWithAll[] = $newsWithAllElement;
        }
        return $newsWithAll;
    }

    public static function findNewsById(int $newsId, array $columns = null)
    {
        if ($columns == null)
            return self::findFirst(['news_id = :newsId:',
                'bind' => ['newsId' => $newsId]]);
        else {
            return self::findFirst(['columns' => $columns, 'news_id = :newsId:',
                'bind' => ['newsId' => $newsId]]);
        }
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

    /**
     * Build an array with only public data
     *
     * @return array
     */
    public function getPublicInfo()
    {
        $toRet = [];
        foreach (self::publicColumns as $info)
            $toRet[$info] = $this->$info;
        return $toRet;
    }

    public static function getPublicInfoFromArray(array $news_data)
    {
        $toRet = [];
        foreach (self::publicColumns as $info)
            $toRet[$info] = $news_data[$info];
        return $toRet;
    }
}
