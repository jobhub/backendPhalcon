<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 02.05.2018
 * Time: 11:45
 */

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class ReviewsAPIController extends Controller
{
    public function indexAction($userId){
        if ($this->request->isGet()) {
            //$today = date("Y-m-d");
            $query = $this->modelsManager->createQuery('SELECT * FROM reviews INNER JOIN userinfo ON reviews.userId_subject=userinfo.userId 
                WHERE reviews.userId_object = :userId:');

            $reviews = $query->execute(
                [
                    'userId' => "$userId"
                ]
            );
            return json_encode($reviews);
        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}