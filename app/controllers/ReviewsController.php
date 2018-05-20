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



    }
}