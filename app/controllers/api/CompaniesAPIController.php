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
            $company->setTin($this->request->getPost("TIN"));
            $company->setRegionId($this->request->getPost("regionId"));
            $company->setWebSite($this->request->getPost("webSite"));
            $company->setEmail($this->request->getPost("email"));

            if($this->request->getPost("isMaster") && $this->request->getPost("isMaster") != 0) {

                if($auth['role'] != ROLE_MODERATOR){
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Ошибка доступа']
                        ]
                    );
                    return $response;
                }

                $company->setIsMaster(true);

                if($this->request->getPost("userId"))
                    $company->setUserid($this->request->getPost("userId"));
            }
            else {
                $company->setIsMaster(0);
                $company->setUserid($userId);
            }

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

    public function deleteCompanyAction($companyId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirstByCompanyId($companyId);

            if ($company && ($company->getUserId() == $userId || $auth['role'] == ROLE_MODERATOR)) {
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

            if (!$company || ($company->getUserId() != $userId && $auth['role']!= ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $company->setName($this->request->getPut("name"));
            $company->setFullname($this->request->getPut("fullName"));
            $company->setTin($this->request->getPut("TIN"));
            $company->setRegionId($this->request->getPut("regionId"));
            $company->setWebSite($this->request->getPut("webSite"));
            $company->setEmail($this->request->getPut("email"));

            if($this->request->getPut("isMaster") && $this->request->getPut("isMaster") != 0) {

                if($auth['role'] != ROLE_MODERATOR){
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Ошибка доступа']
                        ]
                    );
                    return $response;
                }

                $company->setIsMaster(true);

                if($this->request->getPut("userId"))
                    $company->setUserid($this->request->getPut("userId"));
            }
            else {
                $company->setIsMaster(0);
                $company->setUserid($userId);
            }

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

    /**
     * Делает указанного пользователя менеджером компании
     *
     * @method POST
     *
     * @params userId, companyId
     *
     * @return string - json array - объект Status
     */
    public function setManagerAction(){
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if(!Companies::checkUserHavePermission($userId,$this->request->getPost('companyId'), 'addManager')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $companyManager = new CompaniesManagers();
            $companyManager->setUserId($this->request->getPost('userId'));
            $companyManager->setCompanyId($this->request->getPost('companyId'));


            if (!$companyManager->save()) {
                $errors = [];
                foreach ($companyManager->getMessages() as $message) {
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

    /**
     * Удаляет пользователя из менеджеров компании
     *
     * @method DELETE
     *
     * @param $userManagerId
     * @param $companyId
     *
     * @return string - json array - объект Status
     */
    public function deleteManagerAction($companyId,$userManagerId){
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if(!Companies::checkUserHavePermission($userId,$companyId, 'deleteManager')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $companyManager = CompaniesManagers::findFirst(['companyId = :companyId: AND userId = :userId:',
                'bind' => ['companyId' => $companyId, 'userId' => $userManagerId]]);

            if(!$companyManager){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Пользователь не является менеджером компании']
                    ]
                );
                return $response;
            }


            if (!$companyManager->delete()) {
                $errors = [];
                foreach ($companyManager->getMessages() as $message) {
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
