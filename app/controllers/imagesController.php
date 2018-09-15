<?php
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class imagesController extends Controller
{
    public function indexAction($name)
    {
        $name2 = BASE_PATH.'/img/'.$name;
        $fp = fopen($name2, 'rb');

        header("Content-Type: image/png");
        header("Content-Length: " . filesize($name2));

        fpassthru($fp);
        exit;
    }

    public function categoriesAction($name)
    {
        $name2 = BASE_PATH.'/img/categories/'.$name;
        $fp = fopen($name2, 'rb');

        header("Content-Type: image/png");
        header("Content-Length: " . filesize($name2));

        fpassthru($fp);
        exit;
    }

    public function usersAction($name)
    {
        //Типа проверка доступа
        $name2 = BASE_PATH.'/img/users/'.$name;
        $fp = fopen($name2, 'rb');

        header("Content-Type: image/png");
        header("Content-Length: " . filesize($name2));

        fpassthru($fp);
        exit;
    }

    public function reviewsAction($name)
    {
        //Типа проверка доступа
        $name2 = BASE_PATH.'/img/reviews/'.$name;
        $fp = fopen($name2, 'rb');

        header("Content-Type: image/png");
        header("Content-Length: " . filesize($name2));

        fpassthru($fp);
        exit;
    }

    public function eventsAction($name)
    {
        //Типа проверка доступа
        $name2 = BASE_PATH.'/img/events/'.$name;
        $fp = fopen($name2, 'rb');

        header("Content-Type: image/png");
        header("Content-Length: " . filesize($name2));

        fpassthru($fp);
        exit;
    }

    public function servicesAction($name)
    {
        //Типа проверка доступа
        $name2 = BASE_PATH.'/img/services/'.$name;
        $fp = fopen($name2, 'rb');

        header("Content-Type: image/png");
        header("Content-Length: " . filesize($name2));

        fpassthru($fp);
        exit;
    }

    public function companiesAction($name)
    {
        //Типа проверка доступа
        $name2 = BASE_PATH.'/img/companies/'.$name;
        $fp = fopen($name2, 'rb');

        header("Content-Type: image/png");
        header("Content-Length: " . filesize($name2));

        fpassthru($fp);
        exit;
    }
}