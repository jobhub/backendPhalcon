<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class PhonesAPIController extends Controller
{
    /**
     * Добавляет телефон для указанной компании
     * @method POST
     * @param integer companyId, string phone или integer phoneId
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function addPhoneToCompanyAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirstByCompanyId($this->request->getPost("companyId"));

            if ($company && ($company->getUserId() == $userId || $auth['role'] == ROLE_MODERATOR)) {
                $this->db->begin();
                if ($this->request->getPost("phone")) {

                    //Создаем новый
                    $phone = new Phones();
                    $phone->setPhone($this->request->getPost("phone"));

                    if (!$phone->save()) {
                        $this->rollback();
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
                } else if ($this->request->getPost("phoneId")) {
                    $phone = Phones::findFirstByPhoneId($this->request->getPost("phoneId"));

                    if (!$phone) {
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => ['телефона с таким id не существует']
                            ]
                        );
                        return $response;
                    }


                } else {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Нужно указать номер телефона или id существующего в параметрах \'phone\', \'phoneId\'']
                        ]
                    );
                    return $response;
                }


                $phoneCompany = new PhonesCompanies();

                $phoneCompany->setCompanyId($company->getCompanyId());
                $phoneCompany->setPhoneId($phone->getPhoneId());

                if (!$phoneCompany->save()) {

                    $this->db->rollback();
                    $errors = [];
                    foreach ($phoneCompany->getMessages() as $message) {
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

                $this->db->commit();

                $response->setJsonContent(
                    [
                        'status' => STATUS_OK
                    ]
                );
                return $response;

            } else {
                $response->setJsonContent(
                    [
                        'status' => STATUS_WRONG,
                        'errors' => ['permission error']
                    ]
                );
                return $response;
            }
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет телефон для указанной точки оказания услуг
     * @method POST
     * @param integer pointId, string phone или integer phoneId
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function addPhoneToTradePointAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $point = TradePoints::findFirstByPointId($this->request->getPost("pointId"));

            if (!$point) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая точка оказания услуг не существует']
                    ]
                );
                return $response;
            }

            $company = $point->companies;

            if (!$company || !$point ||
                ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR && $point->getUserManager() != $userId)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $this->db->begin();

            if ($this->request->getPost("phone")) {

                //Создаем новый
                $phone = new Phones();
                $phone->setPhone($this->request->getPost("phone"));

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

            } else if ($this->request->getPost("phoneId")) {
                $phone = Phones::findFirstByPhoneId($this->request->getPost("phoneId"));

                if (!$phone) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['телефона с таким id не существует']
                        ]
                    );
                    return $response;
                }

            } else {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нужно указать номер телефона или id существующего в параметрах \'phone\', \'phoneId\'']
                    ]
                );
                return $response;
            }

            $phonePoint= new PhonesPoints();

            $phonePoint->setPointId($point->getPointId());
            $phonePoint->setPhoneId($phone->getPhoneId());

            if (!$phonePoint->save()) {

                $this->db->rollback();
                $errors = [];
                foreach ($phonePoint->getMessages() as $message) {
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

            $this->db->commit();

            $response->setJsonContent(
                [
                    'status' => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Убирает телефон из списка телефонов компании
     *
     * @method DELETE
     *
     * @param (Обязательные) int $phoneId
     * @param int $companyId
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deletePhoneFromCompanyAction($phoneId, $companyId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $phonesCompany = PhonesCompanies::findFirst(["companyId = :companyId: and phoneId = :phoneId:", "bind" =>
            ["companyId" => $companyId,
                "phoneId" => $phoneId]
            ]);

            if(!$phonesCompany){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Телефон не существует']
                    ]
                );
                return $response;
            }

            $company = $phonesCompany->companies;

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

            if (!$phonesCompany->delete()) {
                $errors = [];
                foreach ($phonesCompany->getMessages() as $message) {
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
            //TODO удаление телефона из таблицы Phones, если нет ссылок.

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
