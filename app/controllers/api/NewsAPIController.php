<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Контроллер для работы с новостями
 */
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
            $response = new Response();

            /*$favCompanies = FavoriteCompanies::findByUserid($userId);
            $favUsers = Favoriteusers::findByUsersubject($userId);

            $query = '';
            foreach ($favCompanies as $favCompany){
                if($query != '')
                    $query.=' OR ';
                $query .= '(subjectid = ' . $favCompany->getCompanyId() . ' AND subjecttype = 1)';
            }

            foreach ($favUsers as $favUser){
                if($query != '')
                    $query.=' OR ';
                $query .= '(subjectid = ' . $favUser->getUserObject() . ' AND subjecttype = 0)';
            }

            $news = News::find([$query, "order" => "News.date DESC"]);*/

            $query = $this->db->prepare("SELECT * FROM ((SELECT * FROM public.news n INNER JOIN public.\"favoriteCompanies\" favc
                    ON (n.subjectid = favc.companyid AND n.subjecttype = 1)
                    WHERE favc.userid = :userId)
                    UNION
                    (SELECT * FROM public.news n INNER JOIN public.\"favoriteUsers\" favu
                    ON (n.subjectid = favu.userobject AND n.subjecttype = 0)
                    WHERE favu.usersubject = :userId)) as foo
                    ORDER BY foo.date desc");

            $result = $query->execute([
                'userId' => $userId,
            ]);

            $news = $query->fetchAll(\PDO::FETCH_ASSOC);

            $response->setJsonContent($news);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Создает новость компании или пользователя (в зависимости от subjectType)
     *
     * @method POST
     *
     * @params int subjectType, int subjectId (если subjectType = 0 можно не передавать), string newText
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
            if ($this->request->getPost('subjectType') == 0 || $this->request->getPost('subjectType') == null) {
                //Значит все просто
                $new->setSubjectId($userId);
                $new->setSubjectType(0);
            } else if ($this->request->getPost('subjectType') == 1) {

                if (!Companies::checkUserHavePermission($userId, $this->request->getPost('subjectId'), 'addNew')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }
                $company = Companies::findFirstByCompanyid($this->request->getPost('subjectId'));
                $new->setSubjectId($company->getCompanyId());
                $new->setSubjectType($this->request->getPost('subjectType'));
            }

            $new->setDate(date('Y-m-d H:i:s'));

            $new->setNewText($this->request->getPost('newText'));

            if (!$new->save()) {
                $errors = [];
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

            $new = News::findFirstByNewid($newId);
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
            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $new->getSubjectId(), $new->getSubjectType(), 'deleteNew')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
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
     * @params int newId, string newText
     *
     * @return string - json array объекта Status
     */
    public function editNewAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $new = News::findFirstByNewid($this->request->getPut('newId'));
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
            if ($new->getSubjectType() == 0) {

                if ($new->getSubjectId() != $userId && auth['role'] != ROLE_MODERATOR) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

            } else if ($new->getSubjectType() == 1) {

                $company = Companies::findFirstByCompanyid($new->getSubjectId());

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
     * @param $companyId
     *
     * @return string - json array объектов news или Status, если ошибка
     */
    public function getOwnNewsAction($companyId = null)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if ($companyId != null) {
                //Возвращаем новости компании
                $company = Companies::findFirstByCompanyid($companyId);

                if (!$company || ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $news = News::findBySubject($companyId, 1, 'News.date DESC');
            } else {
                //Возвращаем новости текущего пользователя

                $news = News::findBySubject($userId, 0, 'News.date DESC');
            }
            $response->setJsonContent($news);
            return $response;

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
     * @param $subjectId , $subjecttype
     *
     * @return string - json array объектов news или Status, если ошибка
     */
    public function getSubjectNewsAction($subjectId, $subjecttype)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $news = News::findBySubject($subjectId, $subjecttype, 'News.date DESC');


            $response->setJsonContent($news);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
