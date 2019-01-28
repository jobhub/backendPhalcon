<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http403Exception;
use App\Libs\SupportClass;
use App\Models\Accounts;
use App\Models\LikesNews;
use App\Models\Users;
use App\Models\Userinfo;
use App\Models\PhonesUsers;
use App\Models\ImagesUsers;
use App\Models\News;
use App\Models\FavoriteUsers;
use App\Models\FavoriteCompanies;

use App\Services\ImageService;
use App\Services\AbstractService;
use App\Services\UserService;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class UserinfoAPIController
 * Контроллер ля проведения тестов
 */
class TestController extends AbstractController
{

    public function addAccountsAction()
    {
        $this->db->begin();
        $users = Users::find();

        foreach ($users as $user) {
            $account = new Accounts();

            $account->setUserId($user->getUserId());

            if (!$account->save()) {
                $this->db->rollback();
                SupportClass::getErrorsWithException($account, 100001, 'Не удалось создать аккаунт');
            }
        }


        $this->db->commit();

        return self::successResponse('All accounts successfully created');
    }

    public function addNewsAction()
    {
        $this->db->begin();
        $users = Accounts::find();
        $count_general = 0;
        foreach ($users as $user) {
            $count = 5;

            for ($i = 0; $i < $count; $i++) {
                $data = [];
                $data['news_text'] = 'Text for news ' . $count_general;
                $data['title'] = 'Title for news ' . $count_general;
                $data['account_id'] = $user->getId();
                $data['publish_date'] = date('Y-m-d H:i:s');

                $news = $this->newsService->createNews($data);

                $count_general++;
            }
        }


        $this->db->commit();

        return self::successResponse('All news successfully created');
    }

    public function getNewsAction($news_id)
    {
        $news_arr = News::findByNewsId($news_id);

        return $news_arr->toArray();
    }

    public function getLikedArrayInCycleAction($news_limit, $users_limit,$offset = 0)
    {
        $news_arr = News::find(['limit' => $news_limit,'offset'=>$offset]);
        $users = Users::find(['limit' => $users_limit]);

        $result = [];

        $time_start = microtime(true);

        foreach ($news_arr as $news) {
            $liked_news = [];

            foreach ($users as $user) {
                $query = $this->db->prepare("select likes from public.news 
                    where news_id = :news_id and likes && :user_id");

                $query->execute([
                    'user_id' => '{' . $user->getUserId() . '}',
                    'news_id' => $news->getNewsId(),
                ]);
                /*$news_arr = News::findFirst(['conditions'=>'news_id = :news_id: and likes && :user_id:',
                    'columns'=>'likes','bind'=>['news_id' => $news_id,'user_id' => $user_id]]);*/
                $liked = $query->fetchAll(\PDO::FETCH_ASSOC);
                /*if ($news_arr) {
                    $liked_news[$user->getUserId()] = true;
                } else {
                    $liked_news[$user->getUserId()] = false;
                }*/
            }/*
            $result[$news->getNewsId()] = $liked_news;*/
        }

        $time_end = microtime(true);

        /*$str_array = json_encode($result);

        $file = fopen('news_result.txt', 'w');

        if ($file != null && $file)
            fwrite($file, $str_array);*/

        return ['time' => $time_end - $time_start];
    }

    public function getLikedTableInCycleAction($news_limit, $users_limit,$offset = 0)
    {
        $news_arr = News::find(['limit' => $news_limit,'offset'=>$offset]);
        $users = Users::find(['limit' => $users_limit]);

        $result = [];

        $time_start = microtime(true);

        foreach ($news_arr as $news) {
            $liked_news = [];

            foreach ($users as $user) {
                /*$liked = LikesNews::findFirst(['news_id = :news_id: and user_id = :user_id:',
                    'bind' => ['news_id' => $news->getNewsId(), 'user_id' => $user->getUserId()]]);*/
                $query = $this->db->prepare("select * from public.likes_news 
                    where news_id = :news_id and user_id = :user_id");

                $query->execute([
                    'user_id' => $user->getUserId(),
                    'news_id' => $news->getNewsId(),
                ]);

                /*if ($liked) {
                    $liked_news[$user->getUserId()] = true;
                } else {
                    $liked_news[$user->getUserId()] = false;
                }*/
            }
            /*$result[$news->getNewsId()] = $liked_news;*/
        }

        $time_end = microtime(true);

        /*$str_array = json_encode($result);

        $file = fopen('news_result.txt', 'w');

        if ($file != null && $file)
            fwrite($file, $str_array);*/

        return ['time' => $time_end - $time_start];
    }

    public function getLikedArrayAction($news_id, $user_id)
    {
        $query = $this->db->prepare("select likes from public.news where news_id = :news_id and likes && :user_id"
        );

        try {
            $query->execute([
                'user_id' => '{' . $user_id . '}',
                'news_id' => $news_id,
            ]);
        } catch (\Exception $e) {
            echo $e;
        }
        /*$news_arr = News::findFirst(['conditions'=>'news_id = :news_id: and likes && :user_id:',
            'columns'=>'likes','bind'=>['news_id' => $news_id,'user_id' => $user_id]]);*/
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function getLikedTableAction($news_id, $user_id)
    {
        $news_arr = LikesNews::findFirst(['news_id = :news_id: and user_id = :user_id:',
            'bind' => ['news_id' => $news_id, 'user_id' => $user_id]]);

        return $news_arr->toArray();
    }

    public function addArrayLikeAction($news_id, $user_id)
    {
        $news_arr = News::findFirstByNewsId($news_id);

        $likes = $news_arr->getLikes();

        $likes = SupportClass::translateInPhpArrFromPostgreArr($likes);

        $likes[] = $user_id;

        $news_arr->setLikes(SupportClass::to_pg_array($likes));

        $news_arr->update();

        return $this->successResponse('All ok');
    }

    public function addLikesAction($offset, $countNews)
    {
        $this->db->begin();
        $news_arr = News::find();
        $i = 0;
        /*$file = fopen('progress.txt','w');*/

        $this->db->commit();
        for ($i = intval($offset); $i < count($news_arr) && $i < intval($countNews) + intval($offset); $i++) {
            if ($i < count($news_arr) * 0.75)
                $count = rand(10, 100);
            elseif ($i < count($news_arr) * 0.95)
                $count = rand(500, 5000);
            else
                $count = rand(5000, 15000);
            $news = $news_arr[$i];

            $users = [];
            for ($j = 0; $j < $count; $j++) {
                $rand_user = rand(12, 20011);
                $users[$rand_user] = $rand_user;
            }
            $likes = [];
            foreach ($users as $user_id) {
                $likes[] = $user_id;

                $like_object = new LikesNews();
                $like_object->setNewsId($news->getNewsId());
                $like_object->setUserId($user_id);
                $like_object->create();
            }

            $news->setLikes(SupportClass::to_pg_array($likes));

            $news->update();

            /*fwrite($file,"For news number ".$i." likes created\r\n");
            fflush($file);*/
        }
        /*fclose($file);*/

        return self::successResponse('All likes successfully created');
    }

    public function addUsersAction()
    {
        /*$userId = $this->getUserId();

        if ($userId != 6) {
            throw new Http403Exception('Permission error');
        }*/
        $users = [];

        $count = 20000;

        for ($i = 0; $i < $count; $i++) {
            $user['password'] = '12345678';
            $user['email'] = 'email' . $i . '@mail.comru';
            $users[] = $user;
        }

        $this->db->begin();
        foreach ($users as $userArr) {
            $user = new Users();
            $user->setActivated(true);
            $user->setEmail($userArr['email']);
            $user->setPassword($userArr['password']);
            $user->setRole(ROLE_USER);

            if (!$user->save()) {
                $this->db->rollback();
                SupportClass::getErrorsWithException($user, 100000, 'Не удалось создать пользователя');
            }
        }
        $this->db->commit();

        return self::successResponse('All users successfully created');
    }

    public function sendMessageAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        //Пока, если код существует, то просто перезаписывается
        try {
            $this->resetPasswordService->sendMail('reset_code_letter', 'emails/reset_code_letter',
                    ['resetcode' => '1234',
                    'deactivate' => '1234',
                    'email' => $data['email']],'Подтвердите сброс пароля');
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AbstractService::ERROR_UNABLE_SEND_TO_MAIL:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Code for reset password successfully sent');
    }
}