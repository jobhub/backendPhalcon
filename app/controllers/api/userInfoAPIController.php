<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

class UserinfoAPIController extends Controller
{

    public function indexAction()
    {
        $auth = $this->session->get("auth");
        if ($this->request->isPost() || true) {
            $response = new Response();

                $userinfo = Userinfo::findFirstByuserId($auth['id']);
                if (!$userinfo) {

                    $response->setJsonContent(
                        [
                            "status" => "FAIL"
                        ]);

                    return $response;
                }


                return json_encode($userinfo);
        }
        else{
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}