<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

class ServicesAPIController extends Controller
{

    /**
     * Возвращает все услуги заданной компании
     *
     * @method GET
     *
     * @param $companyId
     *
     * @return string - json array услуг (Services).
     */
    public function getServicesForCompanyAction($companyId)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {

            $services = Services::findByCompanyId($companyId);

            return json_encode($services);
        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }

    }

    /**
     * Удаляет указанную услугу
     *
     * @method DELETE
     *
     * @param $serviceId
     * @return Response - с json массивом в формате Status
     */
    public function deleteServiceAction($serviceId)
    {
        if($this->request->isDelete()){
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceId($serviceId);

            if(!$service){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            $company = Companies::findFirstByCompanyId($service->getCompanyId());

            if (!$company || ($company->getUserId() != $userId && $auth['role']!= ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if(!$service->delete()){
                foreach ($service->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }

                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;
        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Редактирует указанную услугу
     *
     * @method PUT
     *
     * @param serviceId, description, priceMin, priceMax (или же вместо них просто price)
     * @return Response - с json массивом в формате Status
     */
    public function editServiceAction(){
        if($this->request->isPut() && $this->session->get('auth')){
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceId($this->request->getPut("serviceId"));

            if(!$service){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            $company = Companies::findFirstByCompanyId($service->getCompanyId());

            if (!$company || ($company->getUserId() != $userId && $auth['role']!= ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $service->setDescription($this->request->getPut("description"));
            if($this->request->getPut("price")){
                $service->setPriceMin($this->request->getPut("price"));
                $service->setPriceMax($this->request->getPut("price"));
            } else{
                $service->setPriceMin($this->request->getPut("priceMin"));
                $service->setPriceMax($this->request->getPut("priceMax"));
            }

            if (!$service->save()) {
                foreach ($service->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет новую услугу к компании
     *
     * @method POST
     *
     * @params (обязательные) companyId
     * @params (необязательные) description, priceMin, priceMax (или же вместо них просто price)
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addServiceAction(){
        if ($this->request->isPost() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $service = new Services();

            $company = Companies::findFirstByCompanyId($this->request->getPost("companyId"));

            if (!$company || ($company->getUserId() != $userId && $auth['role']!= ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $service->setCompanyId($this->request->getPost("companyId"));
            $service->setDescription($this->request->getPost("description"));

            if($this->request->getPost("price")){
                $service->setPriceMin($this->request->getPost("price"));
                $service->setPriceMax($this->request->getPost("price"));
            } else{
                $service->setPriceMin($this->request->getPost("priceMin"));
                $service->setPriceMax($this->request->getPost("priceMax"));
            }

            $service->setDatePublication(date('Y-m-d H:i:s'));

            if (!$service->save()) {
                $errors = [];
                foreach ($service->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Связывает услугу с точкой оказания услуг
     *
     * @method POST
     *
     * @params (обязательные) serviceId, pointId
     *
     * @return string - json array в формате Status - результат операции
     */
    public function linkServiceWithPointAction(){
        if ($this->request->isPost() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceId($this->request->getPost("serviceId"));

            if(!$service){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            $company = Companies::findFirstByCompanyId($service->getCompanyId());

            if (!$company || ($company->getUserId() != $userId && $auth['role']!= ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $servicePoint = new ServicesPoints();
            $servicePoint->setPointId($this->request->getPost("pointId"));
            $servicePoint->setServiceId($this->request->getPost("serviceId"));

            if (!$servicePoint->save()) {
                $errors = [];
                foreach ($servicePoint->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Убирает связь услуги и точки оказания услуг
     *
     * @method DELETE
     *
     * @param $serviceId
     * @param $pointId
     *
     * @return string - json array в формате Status - результат операции
     */
    public function unlinkServiceAndPointAction($serviceId, $pointId){
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceId($serviceId);

            if(!$service){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            $company = Companies::findFirstByCompanyId($service->getCompanyId());

            if (!$company || ($company->getUserId() != $userId && $auth['role']!= ROLE_MODERATOR)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $servicePoint = ServicesPoints::findFirst(['serviceId = :serviceId: AND pointId = :pointId:',
                'bind' => ['serviceId' => $serviceId , 'pointId' => $pointId]]);

            if(!$servicePoint){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Услуга не связана с точкой оказания услуг']
                    ]
                );
                return $response;
            }

            if (!$servicePoint->delete()) {
                $errors = [];
                foreach ($servicePoint->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        }
        else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}