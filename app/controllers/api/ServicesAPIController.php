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
     * Возвращает услуги. Во-первых, принимает тип запроса в параметре typeQuery:
     * 0 - принимает строку userQuery, центральную точку для поиска - center => [longitude => ..., latitude =>  ...],
     * крайнюю точку для определения радиуса - diagonal => [longitude => ..., latitude =>  ...],
     * массив регионов (id-шников) (regionsId). возвращает
     * список услуг и всего им соответствующего;
     * 1 - запрос на получение элементов интеллектуального поиска. Принимает те же данные, что и в 0-вом запросе.
     * Возвращает массив с типом элемента (строкой - 'company', 'service' и 'category'), id элемента и его названием для отображения в строке
     *  ([{type : ..., id : ..., name : ...}, {...}]);
     * 2 - еще один запрос на получение услуг. Принимает id элемента и тип строкой (type), как отдавалось в запрос 1.
     * Возвращает массив услуг, как в 0-вом запросе.
     * 3 - запрос на получение услуг по категориям. Принимает массив категорий categoriesId, центральную и крайнюю точку
     * и массив регионов, как в 0-вом запросе. Возвращает массив услуг, как везде.
     * 4 - запрос для поиска по области. Центральная точка, крайняя точка, массив регионов, которые попадут в область.
     * Возвращает массив услуг, как везде.
     * @method POST
     *
     * @params int typeQuery (обязательно)
     * @params array center (необязательно) [longitude, latiitude]
     * @params array diagonal (необязательно) [longitude, latiitude]
     * @params string type (необязательно) 'company', 'service', 'category'.
     * @params int id (необязательно)
     * @params string userQuery (необязательно)
     * @params array regionsId (необязательно) массив регионов,
     * @params array categoriesId (необязательно) массив категорий,
     *
     * @return string json массив [status, service, company/userinfo,[categories],[tradepoints],[images]] или
     *   json массив [status, [{type : ..., id : ..., name : ...}, {...}]].
     */
    public function getServicesAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();

            if ($this->request->getPost('typeQuery') == 0) {

                if (strlen($this->request->getPost('userQuery')) < 3) {
                    $response->setJsonContent([
                        'status' => STATUS_WRONG,
                        'errors' => ['Слишком маленькая длина запроса']
                    ]);
                    return $response;
                }

                $result = Services::getServicesByQuery($this->request->getPost('userQuery'),
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));


                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;

            } elseif ($this->request->getPost('typeQuery') == 1) {
                $results = Services::getAutocompleteByQuery($this->request->getPost('userQuery'),
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));

                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'autocomplete' => $results,
                ]);
                return $response;
            } elseif ($this->request->getPost('typeQuery') == 2) {
                $result = Services::getServicesByElement($this->request->getPost('type'),
                    array($this->request->getPost('id')),
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));

                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;

            } elseif($this->request->getPost('typeQuery') == 3){

                $categoriesId = $this->request->getPost('categoriesId');

                foreach($categoriesId as $categoryId){
                    $childCategories = Categories::findByParentid($categoryId);
                    foreach($childCategories as $childCategory){
                        $categoriesId[] = $childCategory->getCategoryId();
                    }
                }

                $result = Services::getServicesByElement('category',
                    $categoriesId,
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));

                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;
            } elseif($this->request->getPost('typeQuery') == 4){
                $result = Services::getServicesByQuery('',
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));

                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;
            }

            //$result = Services::getServices($this->request->getPost('categoriesId'));

            $response->setJsonContent([
                'status' => STATUS_WRONG,
                'errors' => ['Неправильно указан тип запроса']
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
    /*public function editImageServiceAction()
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
    }*/

    /**
     * Добавляет новую услугу к субъекту. Если не указана компания, можно добавить категории.
     *
     * @method POST
     *
     * @params (необязательные) массив oldPoints - массив id tradePoint-ов,
     * (необязательные) массив newPoints - массив объектов TradePoints
     * @params (необязательные) companyId, description, name, priceMin, priceMax (или же вместо них просто price)
     *           (обязательно) regionId,
     *           (необязательно) longitude, latitude
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

            if ($this->request->getPost("video"))
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

            $service->setLongitude($this->request->getPost("longitude"));
            $service->setLatitude($this->request->getPost("latitude"));

            $service->setDatePublication(date('Y-m-d H:i:s'));

            if (!$this->request->getPost("regionId") &&
                !($this->request->getPost("oldPoints") && count($this->request->getPost("oldPoints")) != 0)
                && !($this->request->getPost("newPoints") && count($this->request->getPost("newPoints")) != 0)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Услуга должна быть связана либо с регионом, либо с точками оказания услуг']
                    ]
                );
                return $response;
            }

            $service->setRegionId($this->request->getPost("regionId"));
            //$service->setRegionId(1);
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

            if ($this->request->getPost("newPoints")) {
                $points = $this->request->getPost("newPoints");

                foreach ($points as $point) {
                    //$point2 = json_decode($point);
                    /*$_POST['address'] = $point->address;
                    $_POST['name'] = $point->name;
                    $_POST['longitude'] = $point->longitude;
                    $_POST['latitude'] = $point->latitude;

                    $result = $this->TradePointsAPI->addTradePointAction();*/
                    $result = $this->TradePointsAPI->addTradePoint($point);
                    $result = json_decode($result->getContent());

                    if ($result->status != STATUS_OK) {
                        $this->db->rollback();
                        $response->setJsonContent($result);
                        return $response;
                    }
                    foreach ($point->newPhones as $phone) {
                        $_POST['phone'] = $phone;
                        $_POST['pointId'] = $result->pointId;
                        $result2 = $this->PhonesAPI->addPhoneToTradePointAction();
                        $result2 = json_decode($result2->getContent());

                        if ($result2->status != STATUS_OK) {
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

            if (!$this->request->getPost("companyId")) {
                $categories = $this->request->getPost("categories");

                foreach ($categories as $categoryId) {
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

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(),
                $service->getSubjectType(), 'editService')) {
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

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(),
                    $service->getSubjectType(), 'editService')) {
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

            if ($result) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_OK
                    ]
                );
            } else {
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
     * Добавляет все отправленные файлы изображений к услуге. Общее количество
     * фотографий для одной услуги на данный момент не более 10.
     *
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

            if(($countImages + count($files)) > Imagesservices::MAX_IMAGES ){
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для услуги. 
                        Можно сохранить для одной услуги не более чем '.Imagesservices::MAX_IMAGES.' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $countImagesCopy = $countImages;
            $this->db->begin();

            foreach ($files as $file) {
                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $filename = ImageLoader::formFullImageName('services', $imageFormat, $serviceId, $countImages);

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
                $countImages += 1;
            }

            foreach ($files as $file) {
                $result = ImageLoader::loadService($file->getTempName(), $file->getName(), $countImagesCopy, $serviceId);

                if ($result != ImageLoader::RESULT_ALL_OK || $result === null) {
                    if ($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED) {
                        $error = 'Формат одного из изображений не поддерживается';
                    } elseif ($result == ImageLoader::RESULT_ERROR_NOT_SAVED) {
                        $error = 'Не удалось сохранить изображение';
                    } else {
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

    /**
     * Увеличивает на 1 счетчик числа просмотров услуги.
     * @method PUT
     * @params $serviceId
     * @return string - json array в формате Status
     */
    public function incrementNumberOfDisplayForServiceAction()
    {
        if ($this->request->isPut()) {
            $response = new Response();

            $service = Services::findFirstByServiceid($this->request->getPut("serviceId"));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            $service->setNumberOfDisplay($service->getNumberOfDisplay() + 1);

            if (!$service->update()) {
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
     * Возвращает все заказы, которые могут быть связаны с данной услугой.
     *
     * @method GET
     *
     * @param $serviceId
     * @return string - json array tasks
     */
    public function getTasksForService($serviceId)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $service = Services::findFirstByServiceid($serviceId);

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(),
                    $service->getSubjectType(), 'getTasksForSubject')) {

                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $result = Services::getTasksForService($serviceId);

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
     * Возвращает публичную информацию об услуге.
     * Публичный доступ.
     *
     * @method GET
     *
     * @param $serviceId
     *
     * @return string - json array {status, service, [points => {point, [phones]}]}
     */
    public function getServiceInfoAction($serviceId)
    {
        if ($this->request->isGet()) {
            $response = new Response();

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent([
                    'status' => STATUS_WRONG,
                    'errors' => ['Услуга не существует']
                ]);

                return $response;
            }

            $service = $service->clipToPublic();

            $points = Services::getPointsForService($serviceId);

            $images = Imagesservices::findByServiceid($serviceId);

            $points2 = [];
            foreach ($points as $point) {
                $points2['point'] = $point->clipToPublic();
                $points2['phones'] = PhonesPoints::getPhonesForPoint($point->getPointId());
            }

            $response->setJsonContent([
                'status' => STATUS_OK,
                'service' => $service,
                'points' => $points2,
                'images' => $images
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function addImagesToAllServicesAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();

            $services = Services::find();

            foreach ($services as $service) {
                $randnumber = rand(0, 3);

                if ($randnumber > 0) {
                    $imageserv = new Imagesservices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/desert.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
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
                    $randnumber--;
                }

                if ($randnumber > 0) {
                    $imageserv = new Imagesservices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/butterfly.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
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
                    $randnumber--;
                }
                if ($randnumber > 0) {
                    $imageserv = new Imagesservices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/flower.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
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

            $response->setJsonContent([
                'status' => STATUS_OK,
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}