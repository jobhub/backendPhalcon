<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

class NewsAPIController extends Controller
{
    /**
     * Возвращает новости для ленты текущего пользователя
     * Пока прростая логика с выводом только лишь новостей (без других объектов типа заказов, услуг)
     *
     * @method GET
     *
     * @return string - json array с новостями (или их отсутствием)
     */
    public function getNewsAction()
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $favCompanies = FavoriteCompanies::findByUserId($userId);
            $favUsers = Favoriteusers::findByUserSubject($userId);

            $query = '';
            foreach ($favCompanies as $favCompany){
                if($query != '')
                    $query.=' OR ';
                $query .= '(subjectId = ' . $favCompany->getCompanyId() . ' AND newType = 1)';
            }

            foreach ($favUsers as $favUser){
                if($query != '')
                    $query.=' OR ';
                $query .= '(subjectId = ' . $favUser->getUserObject() . ' AND newType = 0)';
            }

            $news = News::find([$query, "order" => "News.date DESC"]);


            return json_encode($news);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Создает новость компании или пользователя (в зависимости от newType)
     *
     * @method POST
     *
     * @param int newType, int subjectId (если newType = 0 можно не передавать), string newText
     *
     * @return string - json array объекта Status
     */
    public function addNewAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $new = new News();
            //проверки
            if ($this->request->getPost('newType') == 0) {
                //Значит все просто
                $new->setSubjectId($userId);
            } else if ($this->request->getPost('newType') == 1) {
                $company = Companies::findFirstByCompanyId($this->request->getPost('subjectId'));

                if (!$company || ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $new->setSubjectId($company->getCompanyId());
            }

            $new->setDate(date('Y-m-d H:i:s'));
            $new->setNewType($this->request->getPost('newType'));
            $new->setNewText($this->request->getPost('newText'));

            if (!$new->save()) {
                foreach ($new->getMessages() as $message) {
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
     * Удаляет указанную новость
     *
     * @method DELETE
     *
     * @param $newId
     *
     * @return string - json array объекта Status
     */
    public function deleteNewAction($newId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $new = News::findFirstByNewId($newId);
            //проверки

            if (!$new) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Новость не существует']
                    ]
                );

                return $response;
            }

            if ($new->getNewType() == 0) {

                if ($new->getSubjectId() != $userId && auth['role'] != ROLE_MODERATOR) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

            } else if ($new->getNewType() == 1) {

                $company = Companies::findFirstByCompanyId($new->getSubjectId());

                if (!$company || ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );

                    return $response;
                }
            }

            if (!$new->delete()) {
                foreach ($new->getMessages() as $message) {
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
     * Редактирует новость.при этом предполагается, что меняться будут только newText и дата новости.
     * Дата устанавливается текущая (на сервере).
     *
     * @method PUT
     *
     * @param int newId, string newText
     *
     * @return string - json array объекта Status
     */
    public function editNewAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $new = News::findFirstByNewId($this->request->getPut('newId'));
            //проверки

            if (!$new) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Новость не существует']
                    ]
                );

                return $response;
            }

            //проверки
            if ($new->getNewType() == 0) {

                if ($new->getSubjectId() != $userId && auth['role'] != ROLE_MODERATOR) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

            } else if ($new->getNewType() == 1) {

                $company = Companies::findFirstByCompanyId($new->getSubjectId());

                if (!$company || ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );

                    return $response;
                }
            }

            //Редактирование
            $new->setDate(date('Y-m-d H:i:s'));
            $new->setNewText($this->request->getPut('newText'));

            if (!$new->save()) {
                foreach ($new->getMessages() as $message) {
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
     * Возвращает новости текущего пользователя/указанной компании
     *
     * @method GET
     *
     * @param (Необязательный)$companyId
     *
     * @return string - json array объектов news или Status, если ошибка
     */
    public function getOwnNewsAction($companyId = null){
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if($companyId != null){
                //Возвращаем новости компании
                $company = Companies::findFirstByCompanyId($companyId);

                if (!$company || ($company->getUserId() != $userId && $auth['role']!= ROLE_MODERATOR)) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }


                $news = News::find(['subjectId = :companyId: AND newType = 1','bind' => ['companyId' => $companyId],
                    'order' => 'News.date DESC']);
            } else{
                //Возвращаем новости текущего пользователя

                $news = News::find(['subjectId = :userId: AND newType = 0','bind' => ['userId' => $userId],
                    'order' => 'News.date DESC']);
            }

            return json_encode($news);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает новости указанного объекта
     *
     * @method GET
     *
     * @param $subjectId, $newType
     *
     * @return string - json array объектов news или Status, если ошибка
     */
    public function getSubjectNewsAction($subjectId, $newType){
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if($newType == 1){
                //Возвращаем новости компании
                $company = Companies::findFirstByCompanyId($subjectId);

                if (!$company) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Такая компания не существует']
                        ]
                    );
                    return $response;
                }

                $news = News::find(['subjectId = :companyId: AND newType = 1','bind' => ['companyId' => $subjectId],
                    'order' => 'News.date DESC']);

            } else if($newType == 0){
                //Возвращаем новости пользователя

                $user = Users::findFirstByUserId($subjectId);

                if (!$user) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Такой пользователь не существует']
                        ]
                    );
                    return $response;
                }

                $news = News::find(['subjectId = :userId: AND newType = 0','bind' => ['userId' => $subjectId],
                    'order' => 'News.date DESC']);
            }

            return json_encode($news);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
