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
     * Добавляет новую услугу к субъекту
     *
     * @method POST
     *
     * @params (необязательные) массив points - массив id tradePoint-ов
     * @params (необязательные) companyId, description, name, priceMin, priceMax (или же вместо них просто price)
     *           (обязательно) regionId
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

            $service->setDescription($this->request->getPost("description"));
            $service->setName($this->request->getPost("name"));

            if ($this->request->getPost("price")) {
                $service->setPriceMin($this->request->getPost("price"));
                $service->setPriceMax($this->request->getPost("price"));
            } else {
                $service->setPriceMin($this->request->getPost("priceMin"));
                $service->setPriceMax($this->request->getPost("priceMax"));
            }

            $service->setDatePublication(date('Y-m-d H:i:s'));

            if (!$this->request->getPost("regionId") && !$this->request->getPost("points")) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Услуга должна быть связана либо с регионом, либо с точками оказания услуг']
                    ]
                );
                return $response;
            }

            $service->setRegionId($this->request->getPost("regionId"));

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
            if ($this->request->getPost("points")) {
                $points = $this->request->getPost("points");
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

            return $this->addImagesHandler($service->getServiceId());

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
    public function addImagesHandler($serviceId)
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
            //$this->db->begin();
            foreach($files as $file) {
               /* if (($file->getSize() > 5242880)) {
                    $response->setJsonContent(
                        [
                            "errors" => ['Размер файла слишком большой'],
                            "status" => STATUS_WRONG
                        ]
                    );
                    return $response;
                }*/

                /*$image = new SimpleImage();
                $image->load($file->getTempName());
                $image->resizeToWidth(200);*/

                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $format = $imageFormat;
                if ($imageFormat == 'jpeg' || 'jpg')
                    $imageFormat = IMAGETYPE_JPEG;
                elseif ($imageFormat == 'png')
                    $imageFormat = IMAGETYPE_PNG;
                elseif ($imageFormat == 'gif')
                    $imageFormat = IMAGETYPE_GIF;
                else {
                    $response->setJsonContent(
                        [
                            "error" => ['Данный формат не поддерживается'],
                            "status" => STATUS_WRONG
                        ]
                    );
                    return $response;
                }

                $images = Imagesservices::findByServiceid($serviceId);

                $filename = BASE_PATH . '/img/services/' . hash('crc32', $service->getServiceId()) . '_'
                    . count($images) . '.' . $format;

                $imageFullName = str_replace(BASE_PATH, '', $filename);

                $newimage = new Imagesservices();
                $newimage->setServiceId($serviceId);
                $newimage->setImagePath($imageFullName);

                if (!$newimage->save()) {
                    $errors = [];
                   // $this->db->rollback();
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
            }

            foreach($filenames as $filename){
                $image = new SimpleImage();
                $image->load($filename['tempname']);
                $image->resizeToWidth(200);
                $image->save($filename['name'], $filename['format']);
            }

            //$this->db->commit();

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