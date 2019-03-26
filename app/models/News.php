<?php

namespace App\Models;

use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;
use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class News extends AccountWithNotDeletedWithCascade
{
    const NEWS_TYPE_FORWARD_NEWS = 10;
    const NEWS_TYPE_FORWARD_IMAGE_USER = 11;
    const NEWS_TYPE_FORWARD_SERVICE = 12;
    const NEWS_TYPE_FORWARD_PRODUCT = 13;

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


    protected $likes;

    protected $news_type;

    protected $related_id;

    protected $forwards_count;


    const publicColumns = ['news_id', 'publish_date', 'news_text', 'likes', 'news_type', 'account_id', 'related_id', 'forwards_count'];

    const publicColumnsInStr = 'news_id, publish_date, news_text, likes, news_type, account_id, related_id, forwards_count';

    const DEFAULT_RESULT_PER_PAGE = 10;

    /**
     * @return mixed
     */
    public function getForwardsCount()
    {
        return $this->forwards_count;
    }

    /**
     * @param mixed $forwards_count
     */
    public function setForwardsCount($forwards_count)
    {
        $this->forwards_count = $forwards_count;
    }


    /**
     * @return mixed
     */
    public function getRelatedId()
    {
        return $this->related_id;
    }

    /**
     * @param mixed $related_id
     */
    public function setRelatedId($related_id)
    {
        $this->related_id = $related_id;
    }

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
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'related_id',
            new Callback(
                [
                    "message" => "Id of the relation is incorrect",
                    "callback" => function ($news) {
                        if ($news->getNewsType() == self::NEWS_TYPE_FORWARD_IMAGE_USER)
                            $relation = ImagesUsers::findImageById($news->getRelatedId());
                        else if ($news->getNewsType() == self::NEWS_TYPE_FORWARD_NEWS)
                            $relation = News::findNewsById($news->getRelatedId());
                        else if ($news->getNewsType() == self::NEWS_TYPE_FORWARD_SERVICE)
                            $relation = Services::findServiceById($news->getRelatedId());
                        else if ($news->getNewsType() == self::NEWS_TYPE_FORWARD_PRODUCT)
                            $relation = Products::findProductById($news->getRelatedId());
                        else
                            //$relation = NewsInfo::findById($news->getNewsId());
                            return true;

                        if ($relation)
                            return true;
                        return false;
                    }
                ]
            )
        );


        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("news");
        $this->hasOne('news_id', 'App\Models\NewsInfo', 'news_id', ['alias' => 'NewsInfo']);
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
                $images = ImagesNews::findByObjectId($this->getNewsId());

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

    public static function findNewsForCurrentAccount(Accounts $account, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $str = "SELECT  foo.date, foo.data, foo.relname, foo.object_id FROM (
        (SELECT row_to_json(n.*) as data, 'news' as relname, n.publish_date as date, n.news_id as object_id 
         FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id)
                    INNER JOIN public.favorite_companies favc ON 
                                (a.company_id = favc.object_id)
                    WHERE favc.subject_id = ANY (:ids) and n.publish_date < CURRENT_TIMESTAMP
                    )
        UNION ALL
        (
    select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
    inner join public.accounts a ON (m.account_id = a.id)
    INNER JOIN public.favorite_companies favc ON (a.company_id = favc.object_id)
                where favc.subject_id = ANY (:ids))
      	UNION ALL
        (
            SELECT row_to_json(n.*) as data, 'news' as relname, n.publish_date as date, n.news_id as object_id
                     FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id AND a.company_id is null)
                    INNER JOIN public.favorite_users favu ON (a.user_id = favu.object_id)
                    WHERE favu.subject_id = ANY (:ids) and n.publish_date < CURRENT_TIMESTAMP
        )
        UNION ALL
        (
            select row_to_json(m.*) as data, p.relname, m.forward_date as date, m.object_id
                    from public.forwards_in_news_model m inner join pg_class p ON (m.tableoid = p.oid) 
            inner join public.accounts a ON (m.account_id = a.id and a.company_id is null)
            INNER JOIN public.favorite_users favu ON (a.user_id = favu.object_id)
                        WHERE favu.subject_id = ANY (:ids)
        )
    ) as foo ORDER BY foo.date desc";

        $news = SupportClass::executeWithPagination($str, ['ids' => $account->getRelatedAccounts()], $page, $page_size);

        /*$db = DI::getDefault()->getDb();
        $query = $db->prepare($str);
        $result = $query->execute([
            'ids' => $account->getRelatedAccounts()
        ]);

        $news = $query->fetchAll(\PDO::FETCH_ASSOC);*/


        $news['data'] = News::handleNewsSetWithForwards($news['data']);
        return $news;
    }

    public static function findAllNewsForCurrentUser(Accounts $account, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        /*        $db = DI::getDefault()->getDb();

                $page = $page > 0 ? $page : 1;
                $offset = ($page - 1) * $page_size;*/

        $str = "SELECT * FROM (
        (SELECT n.*
         FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id)
                    INNER JOIN public.favorite_companies favc ON 
                                (a.company_id = favc.object_id)
                    WHERE favc.subject_id = ANY(:ids) and n.publish_date < CURRENT_TIMESTAMP
        )
      	UNION ALL
        (
            SELECT n.*
                     FROM public.news n 
                    INNER JOIN public.accounts a ON (n.account_id = a.id AND a.company_id is null)
                    INNER JOIN public.favorite_users favu ON (a.user_id = favu.object_id)
                    WHERE favu.subject_id = ANY(:ids) and n.publish_date < CURRENT_TIMESTAMP
        )
        UNION ALL
        (select n.*
                    from public.news n
                    where n.account_id = ANY(:ids) and n.deleted = false and n.publish_date < CURRENT_TIMESTAMP  )
              
    ) as foo
                    ORDER BY foo.publish_date desc";

        $news = SupportClass::executeWithPagination($str,
            ['ids' => $account->getRelatedAccounts()], $page, $page_size);

        $news['data'] = News::handleNewsSetWithForwards($news['data']);
        return $news;
    }

    /*public static function findNewsByAccount($accountId)
    {
        $news = News::findByAccount($accountId, 'News.publish_date DESC',
            News::publicColumnsInStr . ', account_id');

        return News::handleNewsFromArray($news);
    }*/

    public static function findNewsByCompany($companyId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        /*$page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;
        $db = DI::getDefault()->getDb();*/

        /*$sql = 'Select foo.date, foo.data, foo.relname, foo.object_id from (
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
                order by foo.date desc';*/

        $query = self::getQueryForFindNewsByCompany($companyId);

        $sql = $query->formSql();

        /*$query = $db->prepare($sql);
        $result = $query->execute([
            'companyId' => $companyId,
            'limit' => $page_size,
            'offset' => $offset,
        ]);

        $news = $query->fetchAll(\PDO::FETCH_ASSOC);

        return self::handleNewsSetWithForwards($news);*/

        $news = SupportClass::executeWithPagination($sql,
            $query->getBind(), $page, $page_size);

        $news['data'] = News::handleNewsSetWithForwards($news['data']);
        return $news;
    }

    public static function findNewsByUser($userId, $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        /*$sql = 'Select foo.date, foo.data, foo.relname, foo.object_id from (
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
                order by foo.date desc';

        $news = SupportClass::executeWithPagination($sql,
            ['userid' => $userId], $page, $page_size);

        $news['data'] = News::handleNewsSetWithForwards($news['data']);
        return $news;*/

        $query = self::getQueryForFindNewsByUser($userId);

        $sql = $query->formSql();

        $news = SupportClass::executeWithPagination($sql,
            $query->getBind(), $page, $page_size);

        $news['data'] = News::handleNewsSetWithForwards($news['data']);
        return $news;
    }

    public static function getQueryForFindNewsByUser($userId)
    {
        return new CustomQuery([
            'where' => 'a.user_id = :userId and n.deleted = false and n.publish_date < CURRENT_TIMESTAMP',
            'order' => 'publish_date desc',
            'columns' => 'n.*',
            'from' => 'public.news n inner join 
		            public.accounts a ON (n.account_id = a.id and a.company_id is null)',
            'bind' => [
                'userId' => $userId
            ],
            'id' => 'news_id']);
    }

    public static function getQueryForFindNewsByCompany($companyId)
    {
        return new CustomQuery([
            'where' => 'a.company_id = :companyId and n.deleted = false and n.publish_date < CURRENT_TIMESTAMP',
            'order' => 'n.publish_date desc',
            'columns' => '*',
            'from' => 'public.news n inner join 
		            public.accounts a ON (n.account_id = a.id)',
            'bind' => [
                'companyId' => $companyId
            ],
            'id' => 'news_id']);
    }

    public static function getIdField()
    {
        return 'news_id';
    }

    /**
     * @param array $newsResultSet - must have fields [date, data, relname, object_id]
     *
     * @return array with news and all forwards for user
     */
    public static function handleNewsSetWithForwards(array $newsResultSet)
    {
        return self::handleNewsFromArray($newsResultSet);
    }

    public static function addForwardData(array $data, array $resultElement)
    {
        $resultElement['forward_text'] = $data['news_text'];
        $resultElement['forward_date'] = $data['publish_date'];
        $resultElement['news_id'] = $data['news_id'];
        $resultElement['is_forward'] = true;

        $account = Accounts::findFirstById($data['account_id']);

        if ($account != null) {
            if ($account->getCompanyId() == null)
                $resultElement['publisher_user'] = $account->getUserInfomations();
            else
                $resultElement['publisher_company'] = $account->getUserInfomations();
        }

        return $resultElement;
    }

    public static function handleNewsFromArray(array $news, $accountId = null)
    {
        $newsWithAll = [];

        if ($accountId == null) {
            $session = DI::getDefault()->get('session');
            $accountId = $session->get('accountId');
        }

        if ($accountId != null) {
            $account = Accounts::findFirstById($accountId);
            if ($account)
                $relatedAccounts = $account->getRelatedAccounts();
        }

        foreach ($news as $newsElement) {
            $newsWithAll[] = self::handleNewsObjectFromArray($newsElement, $relatedAccounts);
        }
        return $newsWithAll;
    }

    public static function handleNewsObjectFromArray(array $newsElement, $relatedAccounts = null)
    {
        switch ($newsElement['news_type']) {
            case self::NEWS_TYPE_FORWARD_SERVICE: {
                $service = Services::findServiceById($newsElement['related_id'])->toArray();

                if (is_null($service))
                    break;

                $newsWithAllElement['service'] = Services::handleServiceForNews($service);

                //$forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                $newsWithAllElement = self::addForwardData($newsElement, $newsWithAllElement);

                break;
            }
            case self::NEWS_TYPE_FORWARD_NEWS: {
                $news = News::findNewsById($newsElement['related_id'], News::publicColumns)->toArray();

                if (is_null($news))
                    break;

                $newsWithAllElement['news'] = News::handleNewsToForwardFromArray($news);

                //$forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                $newsWithAllElement = self::addForwardData($newsElement, $newsWithAllElement);
                break;
            }
            case self::NEWS_TYPE_FORWARD_IMAGE_USER: {
                $imageUser = ImagesUsers::findImageById($newsElement['related_id'])->toArray();

                if (is_null($imageUser))
                    break;

                $newsWithAllElement['image_user'] = ImagesUsers::handleImageForNews($imageUser);

                $newsWithAllElement = self::addForwardData($newsElement, $newsWithAllElement);

                break;
            }
            case self::NEWS_TYPE_FORWARD_PRODUCT: {
                $product = Products::findProductById($newsElement['related_id'])->toArray();
                if (is_null($product))
                    break;

                $newsWithAllElement['product'] = Products::handleProductFromArray($product);

                //$forwardData = SupportClass::translateInPhpArrFromPostgreJsonObject($newsElement['data']);

                $newsWithAllElement = self::addForwardData($newsElement, $newsWithAllElement);

                break;
            }
            default: {
                $newsWithAllElement['news_id'] = $newsElement['news_id'];
                $newsWithAllElement['publish_date'] = $newsElement['publish_date'];
                $newsWithAllElement['news_type'] = $newsElement['news_type'];
                $newsWithAllElement['news_text'] = $newsElement['news_text'];
                $newsWithAllElement['title'] = $newsElement['title'];


                $newsInfo = NewsInfo::findById($newsElement['news_id']);
                $newsWithAllElement['title'] = $newsInfo->getTitle();
                unset($newsWithAllElement['likes']);
                if ($newsElement['account_id'] != null) {
                    $account = Accounts::findFirstById($newsElement['account_id']);
                    if ($account->getCompanyId() == null) {
                        $user = Userinfo::findUserInfoById($account->getUserId(), Userinfo::shortColumns);
                        $newsWithAllElement['publisherUser'] = $user;
                    } else {
                        $company = Companies::findCompanyById($account->getCompanyId(),
                            Companies::shortColumns);
                        $newsWithAllElement['publisherCompany'] = $company;
                    }
                }

                $imagesNews = ImagesModel::findAllImages('App\Models\ImagesNews', $newsElement['news_id']);
                $newsWithAllElement['images'] = $imagesNews;
            }
        };

        $last_comment = CommentsNews::findLastParentComment('App\Models\CommentsNews', $newsElement['news_id']);

        $newsWithAllElement['last_comment'] = $last_comment;

        $newsWithAllElement = LikeModel::handleObjectWithLikes($newsWithAllElement, $newsElement, null, $relatedAccounts);

        $newsWithAllElement['forwards_count'] = $newsElement['forwards_count'];
        $newsWithAllElement = ForwardsInNewsModel::handleObjectWithForwards(
            News::NEWS_TYPE_FORWARD_NEWS, $newsWithAllElement, $newsElement['news_id'], $relatedAccounts);
        unset($newsWithAllElement['forwards_count']);
        $newsWithAllElement['stats']['comments'] = CommentsModel::getCountOfComments('comments_news', $newsElement['news_id']);

        return $newsWithAllElement;
    }

    public static function handleNewsToForwardFromArray(array $newsElement)
    {
        $newsWithAllElement['news_id'] = $newsElement['news_id'];
        $newsWithAllElement['publish_date'] = $newsElement['publish_date'];
        $newsWithAllElement['news_type'] = $newsElement['news_type'];
        $newsWithAllElement['news_text'] = $newsElement['news_text'];
        $newsWithAllElement['title'] = $newsElement['title'];

        $newsInfo = NewsInfo::findById($newsElement['news_id']);
        $newsWithAllElement['title'] = $newsInfo->getTitle();
        unset($newsWithAllElement['likes']);
        if ($newsElement['account_id'] != null) {
            $account = Accounts::findFirstById($newsElement['account_id']);
            if ($account->getCompanyId() == null) {
                $user = Userinfo::findUserInfoById($account->getUserId(), Userinfo::shortColumns);
                $newsWithAllElement['publisherUser'] = $user;
            } else {
                $company = Companies::findCompanyById($account->getCompanyId(),
                    Companies::shortColumns);
                $newsWithAllElement['publisherCompany'] = $company;
            }
        }

        $imagesNews = ImagesModel::findAllImages('App\Models\ImagesNews', $newsElement['news_id']);
        $newsWithAllElement['images'] = $imagesNews;

        return $newsWithAllElement;
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

    public static function getPublicationCount(Accounts $account)
    {
        $db = DI::getDefault()->getDb();

        $sql = 'Select SUM(count) from (
                (  select COUNT(*) count
                    from public.news n
                    where n.account_id = ANY(:ids) and n.deleted = false and n.publish_date < CURRENT_TIMESTAMP  )
                UNION ALL (
                    select COUNT(*) count
                    from public.forwards_in_news_model m
                        where m.account_id = ANY(:ids))
                ) foo';

        $query = $db->prepare($sql);
        $query->execute([
            'ids' => $account->getRelatedAccounts(),
        ]);

        $result = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $result[0]['sum'];
    }
}
