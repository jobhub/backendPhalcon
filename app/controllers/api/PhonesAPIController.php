<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class PhonesAPIController extends Controller
{
    public function addPhonesAction(){
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            //$contact = ContactDetailsCompany::findFirstByCompanyId($this->request->getPost("companyId"));
            $company = Companies::findFirstByCompanyId($this->request->getPost("companyId"));

            if($company->getUserId() == $userId) {
                $phones = json_decode($this->request->getPost('phones'));

                if($phones) {
                    foreach ($phones as $phoneNumber) {
                        $this->db->begin();
                        if(count($phoneNumber) <= 20) {
                            $phone = new Phones();
                            $phone->setPhone($phoneNumber);

                            if (!$phone->save()) {

                                $this->db->rollback();
                                $errors = [];
                                foreach ($phone->getMessages() as $message) {
                                    $errors[] = $message->getMessage();
                                }
                                $response->setJsonContent(
                                    [
                                        "status" => STATUS_WRONG,
                                        "errors" => $errors
                                    ]
                                );
                                return $response;
                            }
                        }
                        else {
                            $this->db->rollback();
                            $response->setJsonContent(
                                [
                                    'status' => STATUS_WRONG,
                                    'errors' => ['Слишком длинный номер телефона']
                                ]
                            );
                            return $response;
                        }
                    }

                    $this->db->commit();

                    $response->setJsonContent(
                        [
                            'status' => STATUS_OK
                        ]
                    );
                    return $response;
                }
                else {
                    $response->setJsonContent(
                        [
                            'status' => STATUS_OK
                        ]
                    );
                    return $response;
                }
            }

            $response->setJsonContent(
                [
                    'status' => STATUS_WRONG,
                    'errors' => ['permission error']
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
