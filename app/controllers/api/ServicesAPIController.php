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
     * @param $subjectId
     * @param $subjectType
     * @return string - json array услуг (Services).
     */
    public function getServicesForSubjectAction($subjectId, $subjectType)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {
            $response = new Response();
            $services = Services::findBySubject($subjectId, $subjectType);
            $response->setJsonContent($services);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }

    }

    /**
     * Возвращает услуги. Если указана categories, то услуги в данной категории.
     * @method GET
     *
     * @param $categories = null
     * @return string json массив [service, company/userinfo,[categories],[tradepoints],[images]]
     */
    public function getServicesAction($categories = null){
        if ($this->request->isGet()) {
            $response = new Response();

            $service = new Services();

            $result = $service->getServices($categories);

            $response->setJsonContent([
                'status' => STATUS_OK,
                'services' => $result
            ]);

            return $response;
        } else {
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
        if ($this->request->isDelete()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'deleteService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$service->delete()) {
                $errors = [];
                foreach ($service->getMessages() as $message) {
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
     * Редактирует указанную услугу
     *
     * @method PUT
     *
     * @params serviceId , description, name, priceMin, priceMax (или же вместо них просто price), regionId
     * @params (необязательные) companyId или userId.
     * @return Response - с json массивом в формате Status
     */
    public function editServiceAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($this->request->getPut("serviceId"));

            if (!$service) {
                //$response->setStatusCode('404', 'Not Found');
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($this->request->getPut("companyId")) {
                $service->setSubjectId($this->request->getPut("companyId"));
                $service->setSubjectType(1);
            } else if ($this->request->getPost("userId")) {
                $service->setSubjectId($this->request->getPut("userId"));
                $service->setSubjectType(0);
            }

            $service->setDescription($this->request->getPut("description"));
            $service->setName($this->request->getPut("name"));

            if ($this->request->getPut("price")) {
                $service->setPriceMin($this->request->getPut("price"));
                $service->setPriceMax($this->request->getPut("price"));
            } else {
                $service->setPriceMin($this->request->getPut("priceMin"));
                $service->setPriceMax($this->request->getPut("priceMax"));
            }

            $service->setRegionId($this->request->getPut("regionId"));

            if (!$service->save()) {
                $errors = [];
                foreach ($service->getMessages() as $message) {
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
     *
     */
    public function editImageServiceAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($this->request->getPost("serviceId"));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $result = $this->addImageHandler($service->getServiceId());

            $result = json_decode($result->getContent());

            if($result->status != STATUS_OK){
                $response->setJsonContent(
                    [
                        "status" => $result->status,
                        "errors" => $result->errors
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
     * Добавляет новую услугу к субъекту. Если не указана компания, можно добавить категории.
     *
     * @method POST
     *
     * @params (необязательные) массив oldPoints - массив id tradePoint-ов,
     * (необязательные) массив newPoints - массив объектов TradePoints
     * @params (необязательные) companyId, description, name, priceMin, priceMax (или же вместо них просто price)
     *           (обязательно) regionId,
     *           (необязательно) если не указана компания, можно указать id категорий в массиве categories.
     *
     * @return string - json array. Если все успешно - [status, serviceId], иначе [status, errors => <массив ошибок>].
     */
    public function addServiceAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $service = new Services();

            if ($this->request->getPost("companyId")) {
                if (!Companies::checkUserHavePermission($userId, $this->request->getPost("companyId"),
                    'addService')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $service->setSubjectId($this->request->getPost("companyId"));
                $service->setSubjectType(1);

            } else {
                $service->setSubjectId($userId);
                $service->setSubjectType(0);
            }
            $description = $this->request->getPost("description");

            if($this->request->getPost("video"))
                $description .= "\n\rВидео: " . $this->request->getPost("video");

            $service->setDescription($description);
            $service->setName($this->request->getPost("name"));

            if ($this->request->getPost("price")) {
                $service->setPriceMin($this->request->getPost("price"));
                $service->setPriceMax($this->request->getPost("price"));
            } else {
                $service->setPriceMin($this->request->getPost("priceMin"));
                $service->setPriceMax($this->request->getPost("priceMax"));
            }

            $service->setDatePublication(date('Y-m-d H:i:s'));

            if (!$this->request->getPost("regionId") &&
                !($this->request->getPost("oldPoints") && count($this->request->getPost("oldPoints"))!=0)
            && !($this->request->getPost("newPoints") && count($this->request->getPost("newPoints"))!=0)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Услуга должна быть связана либо с регионом, либо с точками оказания услуг']
                    ]
                );
                return $response;
            }

            //$service->setRegionId($this->request->getPost("regionId"));
            $service->setRegionId(1);
            $this->db->begin();

            if (!$service->save()) {
                $this->db->rollback();
                $errors = [];
                foreach ($service->getMessages() as $message) {
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

            //
            if ($this->request->getPost("oldPoints")) {
                $points = $this->request->getPost("oldPoints");
                foreach ($points as $point) {
                    $servicePoint = new ServicesPoints();
                    $servicePoint->setServiceId($service->getServiceId());
                    $servicePoint->setPointId($point);

                    if (!$servicePoint->save()) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($servicePoint->getMessages() as $message) {
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
            }

            if($this->request->getPost("newPoints")){
                $points = $this->request->getPost("newPoints");

                foreach ($points as $point) {
                    //$point2 = json_decode($point);
                    $_POST['address'] = $point->address;
                    $_POST['name'] = $point->name;
                    $_POST['longitude'] = $point->longitude;
                    $_POST['latitude'] = $point->latitude;

                    $result = $this->TradePointsAPI->addTradePointAction();
                    $result = json_decode($result->getContent());

                    if($result->status!= STATUS_OK){
                        $this->db->rollback();
                        $response->setJsonContent($result);
                        return $response;
                    }
                    foreach($point->newPhones as $phone) {
                        $_POST['phone'] = $phone;
                        $_POST['pointId'] = $result->pointId;
                        $result2 = $this->PhonesAPI->addPhoneToTradePointAction();
                        $result2 = json_decode($result2->getContent());

                        if($result2->status != STATUS_OK){
                            $this->db->rollback();
                            $response->setJsonContent($result2);
                            return $response;
                        }
                    }

                    $servicePoint = new ServicesPoints();
                    $servicePoint->setServiceId($service->getServiceId());
                    $servicePoint->setPointId($result->pointId);

                    if (!$servicePoint->save()) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($servicePoint->getMessages() as $message) {
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
            }

            if(!$this->request->getPost("companyId")){
                $categories = $this->request->getPost("categories");

                foreach($categories as $categoryId){
                    $userCategory = new UsersCategories();
                    $userCategory->setUserId($userId);
                    $userCategory->setCategoryId($categoryId);

                    if (!$userCategory->save()) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($userCategory->getMessages() as $message) {
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
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    'serviceId' => $service->getServiceId()
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет картинки к услуге
     *
     * @method POST
     *
     * @params (обязательно) serviceId
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addImagesAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $service = new Services();

            $service = Services::findFirstByServiceid($this->request->getPost('serviceId'));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if(!SubjectsWithNotDeleted::checkUserHavePermission($userId,$service->getSubjectId(),
                $service->getSubjectType(), 'editService')){
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $result = $this->addImagesHandler($service->getServiceId());

            return $result;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Удаляет картинку из списка картинок услуги
     *
     * @method DELETE
     *
     * @param $imageId
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageAction($imageId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $image = Imagesservices::findFirstByImageid($imageId);

            if (!$image) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор картинки'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $service = Services::findFirstByServiceid($image->getServiceId());

            if(!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId,$service->getSubjectId(),
                $service->getSubjectType(), 'editService')){
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$image->delete()) {
                $errors = [];
                foreach ($image->getMessages() as $message) {
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

            $result = ImageLoader::delete($image->getImagePath());

            if($result){
                $response->setJsonContent(
                    [
                        "status" => STATUS_OK
                    ]
                );
            } else{
                $response->setJsonContent(
                    [
                        "errors" => ['Не удалось удалить файл'],
                        "status" => STATUS_WRONG
                    ]
                );
            }
            return $response;

        } else {
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
    public function linkServiceWithPointAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($this->request->getPost("serviceId"));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(),
                'linkServiceWithPoint')) {
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
     * Убирает связь услуги и точки оказания услуг
     *
     * @method DELETE
     *
     * @param $serviceId
     * @param $pointId
     *
     * @return string - json array в формате Status - результат операции
     */
    public function unlinkServiceAndPointAction($serviceId, $pointId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(),
                'unlinkServiceWithPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $servicePoint = ServicesPoints::findByIds($serviceId, $pointId);

            if (!$servicePoint) {
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
     * Подтверждает выполнить заявку на оказание услуги
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return Response - с json массивом в формате Status
     */
    public function confirmRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_WAITING_CONFIRM) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя подтвердить заказ на данном этапе']
                    ]
                );
                return $response;
            }

            $request->setStatus(STATUS_EXECUTING);

            if (!$request->save()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
     * Предоставляющий услугу субъект утверждает, что выполнил заявку
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return Response - с json массивом в формате Status
     */
    public function performRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_EXECUTING) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя завершить заказ на данном этапе']
                    ]
                );
                return $response;
            }

            $request->setStatus(STATUS_EXECUTED_EXECUTOR);

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
     * Заказчик отменяет заявку.
     *
     * @method PUT
     *
     * @param requestId
     *
     * @return string - json array в формате Status
     */
    public function rejectRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_WAITING_CONFIRM &&
                $request->getStatus() != STATUS_EXECUTING &&
                $request->getStatus() != STATUS_EXECUTED_EXECUTOR &&
                $request->getStatus() != STATUS_EXECUTED_CLIENT) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя отказаться от заказа на данном этапе']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() == STATUS_WAITING_CONFIRM)
                $request->setStatus(STATUS_NOT_CONFIRMED);
            else {
                $request->setStatus(STATUS_NOT_EXECUTED);
            }

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
     * Добавляет картинку к услуге.
     * @param $serviceId
     * @return Response с json массивом типа Status
     */
    /*public function addImagesHandler($serviceId)
    {
        $response = new Response();
        include(APP_PATH . '/library/SimpleImage.php');
        // Проверяем установлен ли массив файлов и массив с переданными данными
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $filenames = [];

            $images = Imagesservices::findByServiceid($serviceId);
            $countImages = count($images);

            $this->db->begin();

            foreach($files as $file) {


                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $format = $imageFormat;
                if ($imageFormat == 'jpeg' || 'jpg')
                    $imageFormat = IMAGETYPE_JPEG;
                elseif ($imageFormat == 'png')
                    $imageFormat = IMAGETYPE_PNG;
                elseif ($imageFormat == 'gif')
                    $imageFormat = IMAGETYPE_GIF;
                else {
                    $this->db->rollback();
                    $response->setJsonContent(
                        [
                            "error" => ['Данный формат не поддерживается'],
                            "status" => STATUS_WRONG
                        ]
                    );
                    return $response;
                }

                $filename = BASE_PATH . '/img/services/' . hash('crc32', $service->getServiceId()) . '_'
                    . $countImages . '.' . $format;

                $imageFullName = str_replace(BASE_PATH, '', $filename);

                $newimage = new Imagesservices();
                $newimage->setServiceId($serviceId);
                $newimage->setImagePath($imageFullName);

                if (!$newimage->save()) {
                    $errors = [];
                    $this->db->rollback();
                    foreach ($newimage->getMessages() as $message) {
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
                $filenames[] = ['name' => $filename, 'format' => $imageFormat, 'tempname' => $file->getTempName()];
                $countImages+=1;
            }

            foreach($filenames as $filename){
                $image = new SimpleImage();
                $image->load($filename['tempname']);
                $image->resizeToWidth(200);
                $image->save($filename['name'], $filename['format']);
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
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
    }*/

    /**
     * Добавляет картинку к услуге.
     * @param $serviceId
     * @return Response с json массивом типа Status
     */
    public function addImagesHandler($serviceId)
    {
        include(APP_PATH . '/library/SimpleImage.php');
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $filenames = [];

            $images = Imagesservices::findByServiceid($serviceId);
            $countImages = count($images);
            $countImagesCopy = $countImages;
            $this->db->begin();

            foreach($files as $file) {
                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $filename = ImageLoader::formFullImageName('services',$imageFormat,$serviceId,$countImages);

                $imageFullName = $filename;

                $newimage = new Imagesservices();
                $newimage->setServiceId($serviceId);
                $newimage->setImagePath($imageFullName);

                if (!$newimage->save()) {
                    $errors = [];
                    $this->db->rollback();
                    foreach ($newimage->getMessages() as $message) {
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
                //$filenames[] = ['name' => $filename, 'format' => $imageFormat, 'tempname' => $file->getTempName()];
                $countImages+=1;
            }

            foreach($files as $file) {
                /*$imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);
                $filename = ImageLoader::formImageName($imageFormat,$serviceId,$countImagesCopy);
                $result = ImageLoader::load('services',$file->getTempName(),$filename);*/

                $result = ImageLoader::loadService($file->getTempName(),$file->getName(),$countImagesCopy,$serviceId);

                if($result != ImageLoader::RESULT_ALL_OK || $result === null){
                    if($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED){
                        $error = 'Формат одного из изображений не поддерживается';
                    } elseif($result == ImageLoader::RESULT_ERROR_NOT_SAVED){
                        $error = 'Не удалось сохранить изображение';
                    }
                    else{
                        $error = 'Ошибка при загрузке изображения';
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => [$error]
                        ]
                    );
                    return $response;
                }

                $countImagesCopy++;
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
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
    }
}