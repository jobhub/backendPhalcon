<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http403Exception;
use App\Libs\SupportClass;
use App\Models\Accounts;
use App\Models\Users;
use App\Models\Userinfo;
use App\Models\PhonesUsers;
use App\Models\ImagesUsers;
use App\Models\News;
use App\Models\FavoriteUsers;
use App\Models\FavoriteCompanies;

use App\Services\ImageService;
use App\Services\UserInfoService;
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
                SupportClass::getErrorsWithException($account,100001,'Не удалось создать аккаунт');
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

            for($i = 0; $i < $count; $i++){
                $data = [];
                $data['news_text'] = 'Text for news '.$count_general;
                $data['title'] = 'Title for news '.$count_general;
                $data['account_id'] = $user->getId();
                $data['publish_date'] = date('Y-m-d H:i:s');

                $news = $this->newsService->createNews($data);

                $count_general++;
            }
        }


        $this->db->commit();

        return self::successResponse('All news successfully created');
    }

    public function addLikesAction()
    {
        $this->db->begin();
        $news_arr = News::find();
        $count_general = 0;
        $i = 0;
        for (; $i < count($news_arr)*0.75;$i++) {
            $count = rand(10,100);


        }


        $this->db->commit();

        return self::successResponse('All news successfully created');
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
            $user['email'] = 'email'.$i.'@mail.comru';
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
                SupportClass::getErrorsWithException($user,100000,'Не удалось создать пользователя');
            }
        }
        $this->db->commit();

        return self::successResponse('All users successfully created');
    }
}