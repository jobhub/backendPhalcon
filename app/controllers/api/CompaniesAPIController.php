<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class CompaniesAPIController extends Controller
{
    public function getCompaniesAction()
    {
        if ($this->request->isGet() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $companies = Companies::find(["userId = :userId:", "bind" =>
                ["userId" => $userId]]);

            return json_encode($companies);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function addCompanyAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = new Companies();
            $company->setName($this->request->getPost("name"));
            $company->setFullname($this->request->getPost("fullName"));
            $TIN = $this->request->getPost("TIN");

            $result = preg_match('|[0-9]{12}|',$TIN);
            if(!$result){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Неверно указан ИНН']
                    ]
                );
                return $response;
            }

            $company->setTin($this->request->getPost("TIN"));
            $company->setRegionId($this->request->getPost("regionId"));
            $company->setUserid($userId);

            $this->db->begin();

            if (!$company->save()) {

                $this->db->rollback();

                foreach ($company->getMessages() as $message) {
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

            $_POST['companyId'] = $company->getCompanyId();

            $result = $this->ContactDetailsCompanyCompanyAPI->addContactDetailsAction();

            $result = json_decode($result->getContent());

            if ($result->status != STATUS_OK) {
                $this->db->rollback();
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $result->errors
                    ]
                );
                return $response;
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function deleteCompanyAction($companyId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirstByCompanyId($companyId);

            if ($company && $company->getUserId() == $userId) {
                if (!$company->delete()) {
                    $errors = [];
                    foreach ($company->getMessages() as $message) {
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

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function editCompanyAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirstByCompanyId($this->request->getPut("companyId"));

            if (!$company || $company->getUserId() != $userId) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $company->setName($this->request->getPost("name"));
            $company->setFullname($this->request->getPost("fullName"));
            $company->setTin($this->request->getPost("TIN"));
            $company->setRegionId($this->request->getPost("regionId"));

            if (!$company->save()) {

                foreach ($company->getMessages() as $message) {
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

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
