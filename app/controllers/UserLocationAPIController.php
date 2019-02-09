<?php

namespace App\Controllers;

use App\Services\MarkerService;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Models\UserLocation;
use App\Models\Accounts;
use App\Models\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

use App\Services\PointService;
use App\Services\UserLocationService;

/**
 * Class UserLocationAPIController
 * Контроллер предназначенный исключительно для поиска людей на карте.
 * Содержит методы для установления текущей позиции пользователя,
 * поиска пользователей и получения автокомплита.
 */
class UserLocationAPIController extends AbstractController
{
    /**
     * Устанавливает текущее местоположение текущего пользователя.
     *
     * @access private.
     *
     * @method POST
     * @params latitude;
     * @params longitude;
     * @return string - json array результат операции.
     */
    public function setLocationAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['latitude'] = $inputData->latitude;
        $data['longitude'] = $inputData->longitude;

        $this->db->begin();
        try {
            $userId = self::getUserId();

            $location = UserLocation::findFirstByUserId($userId);

            if($location){
                $data['last_time'] = date('Y-m-d H:i:sO', time());
                $location = $this->userLocationService->changeUserLocation($location, $data);
            } else {
                $location = $this->userLocationService->createUserLocation($data, $userId);
            }
            $location = $this->userLocationService->getUserLocationById($userId);
        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case UserLocationService::ERROR_UNABLE_CREATE_USER_LOCATION:
                case MarkerService::ERROR_UNABLE_CREATE_MARKER:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        $this->db->commit();
        return self::successResponse('User location was successfully created',
            ['user_location' => UserLocation::handleUserLocation($location->toArray())]);
    }

    /**
     * Ищет пользователей по поисковой строке и внутри заданных координат.
     * @access public
     *
     * @method POST
     *
     * @param $page
     * @param $page_size
     *
     * @params string query
     * @params center - [longitude => ..., latitude => ...] - центральная точка
     * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
     * @return string - json array - массив пользователей.
     *          [status, users=>[userid, email, phone, firstname, lastname, patronymic,
     *          longitude, latitude, lasttime,male, birthday,pathtophoto]]
     */
    public function findUsersAction($page = 1, $page_size = UserLocation::DEFAULT_RESULT_PER_PAGE)
    {
        $inputData = json_decode($this->request->getRawBody(),true);
        $data['center'] = $inputData['center'];
        $data['diagonal'] = $inputData['diagonal'];
        $data['query'] = $inputData['query'];

        $data['page'] = $page;
        $data['page_size'] = $page_size;

        return $this->userLocationService->findUsers($data);
    }


    /**
     * Ищет пользователей по поисковой строке и внутри заданных координат.
     * С заданным фильтром.
     * @access public
     *
     * @method POST
     *
     * @param $page
     * @param $page_size
     *
     * @params string query
     * @params center - [longitude => ..., latitude => ...] - центральная точка
     * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
     * @params age_min - минимальный возраст
     * @params age_max - максимальный возраст
     * @params male - пол
     * @params has_photo - фильтр, имеется ли у него фотография
     * @return string - json array - массив пользователей.
     *          [status, users=>[userid, email, phone, firstname, lastname, patronymic,
     *          longitude, latitude, lasttime,male, birthday,pathtophoto]]
     */
    public function findUsersWithFiltersAction($page = 1, $page_size = UserLocation::DEFAULT_RESULT_PER_PAGE)
    {
        $inputData = json_decode($this->request->getRawBody(),true);
        $data['center'] = $inputData['center'];
        $data['diagonal'] = $inputData['diagonal'];
        $data['query'] = $inputData['query'];
        $data['age_min'] = $inputData['age_min'];
        $data['age_max'] = $inputData['age_max'];
        $data['male'] = $inputData['male'];
        $data['has_photo'] = $inputData['has_photo'];

        $data['page'] = $page;
        $data['page_size'] = $page_size;

        return $this->userLocationService->findUsers($data);
    }

    /**
     * Возвращает данные для автокомплита поиска по пользователям.
     *
     * @access public
     *
     * @method POST
     *
     * @param $page
     * @param $page_size
     *
     * @params string query
     * @params center - [longitude => ..., latitude => ...] - центральная точка
     * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
     * @return string - json array - массив пользователей.
     *          [status, users=>[userid, firstname, lastname, patronymic,status]]
     */
    public function getAutoCompleteForSearchAction($page = 1, $page_size = UserLocation::DEFAULT_RESULT_PER_PAGE)
    {
        $inputData = json_decode($this->request->getRawBody(),true);
        $data['center'] = $inputData->center;
        $data['diagonal'] = $inputData->diagonal;
        $data['query'] = $inputData->query;

        $data['page'] = $page;
        $data['page_size'] = $page_size;

        return $this->userLocationService->getAutocomplete($data);
    }

    /**
     * Возвращает данные по id пользователя аналогичные поиску, но без поиска.
     *
     * @access public
     *
     * @method GET
     *
     * @param int $user_id
     * @return string - json array - массив пользователей.
     *          [status, users=>[userid, email, phone, firstname, lastname, patronymic,
     *          longitude, latitude, lasttime,male, birthday,pathtophoto,status]]
     */
    public function getUserByIdAction($user_id)
    {
        return $this->userLocationService->getUserDataWithLocationById($user_id);
    }

    /**
     * Возвращает данные для автокомплита поиска по пользователям и услугам.
     *
     * @access public
     *
     * @method POST
     *
     * @params string query
     * @params center - [longitude => ..., latitude => ...] - центральная точка
     * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
     * @return string - json array - массив пользователей и услуг.
     *          [type, id, name]
     */
    /*public function getAutoCompleteForSearchServicesAndUsersAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['center'] = $inputData->center;
        $data['diagonal'] = $inputData->diagonal;
        $data['query'] = $inputData->query;

        return $this->userLocationService->getAutocomplete($data);


        if ($longitudeHR == -1)
            $services = Services::getAutocompleteByQuery($this->request->getPost('query'),
                null, null,
                $this->request->getPost('regionsId'));
        else
            $services = Services::getAutocompleteByQuery($this->request->getPost('query'),
                $this->request->getPost('center'), $this->request->getPost('diagonal'),
                $this->request->getPost('regionsId'));

        $autocomplete = [];
        $limit = 10;
        $current = 0;
        foreach ($users as $user) {
            if ($current > $limit)
                break;
            $autocomplete[] = ['type' => 'user', 'id' => $user['userid'],
                'name' => $user['firstname'] . ' ' . $user['lastname']
                    . ($user['patronymic'] == null ? '' : ' ' . $user['patronymic']),
                'pathtophoto' => $user['pathtophoto']];
            $current++;
        }

        foreach ($services as $service) {
            if ($current > $limit)
                break;
            $autocomplete[] = $service;
            $current++;
        }

        $response->setJsonContent([
            "status" => STATUS_OK,
            'autocomplete' => $autocomplete
        ]);

        return $response;
    }*/
}