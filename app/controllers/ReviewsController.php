<?php
/**
 * Created by PhpStorm.
 * User: NorD
 * Date: 20.05.2018
 * Time: 16:14
 */

use Phalcon\Mvc\Model\Criteria;

class ReviewsController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('Reviews');
        parent::initialize();
    }

    public function newAction($idObject)
    {
        if(!$this->session->get('coordination'))
        {
            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);
            return;
        }
        $this->tag->setDefault('executor',$this->session->get('executor'));
        $this->tag->setDefault('objectId',$idObject);
        $this->tag->setDefault('subjectId',$this->session->get('subjectId'));
        $this->session->remove('executor');
        $this->session->remove('subjectId');
        $this->session->remove('coordination');
    }

    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }

        $userinfo = Userinfo::findFirstByuserId($this->request->getPost('objectId'));
        if (!$userinfo) {
            $this->flash->error("Пользователь не найден");

            $this->dispatcher->forward([
                'controller' => "auctions",
                'action' => 'index'
            ]);

            return;
        }
        $review=new Reviews();
        $review->setRaiting($this->request->getPost('raiting'));
        $today = date("Y-m-d h:m");
        $review->setReviewDate($today);
        $review->setTextReview($this->request->getPost('textReview'));
        $review->setExecutor($this->request->getPost('executor'));
        $review->setUserIdObject($this->request->getPost('objectId'));
        $review->setUserIdSubject($this->request->getPost('subjectId'));

        if (!$review->save()) {
            foreach ($review->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "reviews",
                'action' => 'new',
                'params' => [$this->request->getPost('objectId')]
            ]);
            return;
        }

        $this->flash->success("Информация сохранена успешно");

        $this->dispatcher->forward([
            'controller' => "auctions",
            'action' => 'index'

        ]);
    }


}