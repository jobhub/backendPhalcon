<?php

namespace App\Middleware;

use App\Controllers\HttpExceptions\Http400Exception;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\DI\FactoryDefault as DI;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use App\Models\Accesstokens;

use App\Libs\SupportClass;

/**
 * CORSMiddleware
 *
 * CORS checking
 */
class JWTMiddleware implements MiddlewareInterface
{

    const ERROR_TOKEN_EXPIRED = 100;
    /**
     * Before anything happens
     *
     * @param Event $event
     * @param Micro $application
     *
     * @returns bool
     */
    public function beforeHandleRoute(Event $event, Micro $application)
    {
        $di = DI::getDefault();
        $tokenRecieved = self::getTokenFromHeader();
        SupportClass::writeMessageInLogFile('token is '.$tokenRecieved);
        $info = json_decode($di->getAuthService()->checkToken($tokenRecieved), true);

        if (!$info) {
            //$this->session->remove('auth');
            SupportClass::writeMessageInLogFile('Не нашел токена в базе, разрушил сессию');

        } else {
            if (strtotime($info['lifetime']) <= time() or true) {

                SupportClass::writeMessageInLogFile('Время действия токена закончилось, разрушил сессию');
                throw new Http400Exception("Token expired",self::ERROR_TOKEN_EXPIRED);

            } else {
                $di->getAuthService()->_registerSessionByData($info,$application);
            }
        }

        if (!$di->getSession()->get('auth')) {
            SupportClass::writeMessageInLogFile('Сессии нет или же переменной в сессии нет. Роль гостя');
            $role = ROLE_GUEST;
        } else {
            SupportClass::writeMessageInLogFile('Сессия есть, роль ' . $di->getSession()->get('auth')['role']);
            $role = $di->getSession()->get('auth')['role'];
        }

    }

    private function convertRequestBody()
    {
        if ($this->request->getJsonRawBody() != null && $this->request->getJsonRawBody() != "") {
            $params = $this->request->getRawBody();
            $params = json_decode($params, true);
            if ($params != null) {
                if ($this->request->isPost()) {
                    foreach ($params as $key => $param) {
                        $_POST[$key] = $param;
                    }
                } else if ($this->request->isPut()) {
                }
            }

        }
    }

    private function notAPIController($controllerName)
    {
        if ($controllerName == 'index' || $controllerName == 'errors'
            || $controllerName == 'images') {
            return true;
        }
        return false;
    }

    public function getAcl()
    {
        //if (!isset($this->persistent->acl))
        {
            $acl = new AclList();

            $acl->setDefaultAction(Acl::DENY);

            // Register roles
            //Должно браться потом из БД
            $roles = [
                'user' => new Role(
                    'User',
                    'Member privileges, granted after sign in.'
                ),
                'guests' => new Role(
                    'Guests',
                    'Anyone browsing the site who is not signed in is considered to be a "Guest".'
                ),
                'user_defective' => new Role(
                    'UserDefective',
                    "Пользователь не заполнивший все поля."
                ),
                'moderator' => new Role(
                    'Moderator',
                    'Any moderators who can role.'
                )
            ];

            foreach ($roles as $role) {
                $acl->addRole($role);
            }

            //Private area resources
            //Тоже надо бы из БД взять
            $privateResources = [
                'CategoriesAPI' => ['getFavourites', 'setFavourite', 'deleteFavourite', 'editRadiusInFavourite'],
                'FavouriteUsersAPI' => ['setFavourite', 'deleteFavourite', 'getFavourites'],
                'NewsAPI' => ['getNews', 'addNews', 'deleteNews', 'editNews', 'getOwnNews', 'getSubjectNews',
                    'addImages', 'deleteImageByName', 'deleteImageById', 'getAllNews',],
                'coordinationAPI' => ['addMessage', 'getMessages', 'selectOffer', 'addTokenId', 'clearTokens', 'finishTask', 'completeTask'],

                'CompaniesAPI' => ['addCompany', 'editCompany', 'deleteCompany', 'setManager', 'deleteManager',
                    'restoreCompany', 'setCompanyLogotype', 'getCompanies', 'deleteCompanyTest'],

                'PhonesAPI' => ['addPhoneToCompany', 'addPhoneToTradePoint', 'deletePhoneFromCompany',
                    'deletePhoneFromTradePoint', 'editPhoneInTradePoint', 'editPhoneInCompany', 'test',
                    'addPhoneToUser', 'deletePhoneFromUser'],
                'TradePointsAPI' => ['addTradePoint', 'getPointsForUserManager', 'getPoints', 'editTradePoint', 'deleteTradePoint'],
                'MessagesAPI' => ['addMessage', 'getMessages', 'getChats', 'getChat'],
                'FavouriteCompaniesAPI' => ['setFavourite', 'deleteFavourite', 'getFavourites'],
                'ServicesAPI' => ['deleteService', 'addService', 'editService',
                    'linkServiceWithPoint', 'unlinkServiceAndPoint', 'confirmRequest', 'performRequest',
                    'rejectRequest', 'editImageService', 'addImages', 'deleteImage', 'getOwnServices',
                    'deleteImageByName'],
                'RequestsAPI' => ['addRequest', 'deleteRequest', 'editRequest', 'getRequests', 'cancelRequest',
                    'confirmPerformanceRequest'],

                'TasksAPI' => ['addTask', 'deleteTask', 'editTask', 'getTasksForCurrentUser', 'selectOffer', 'cancelTask',
                    'confirmPerformanceTask'],
                'OffersAPI' => ['getForTask', 'addOffer', 'getForSubject', 'deleteOffer', 'editOffer', 'getForTask',
                    'confirmOffer', 'rejectOffer', 'performTask'],

                'ReviewsAPI' => ['addReview', 'editReview', 'deleteReview', 'addImages'],
                'EventsAPI' => ['addEvent', 'setImage', 'deleteEvent', 'editEvent'],
                'UserLocationAPI' => ['setLocation'],
                'UserinfoAPI' => ['addImages', 'deleteImage',],
                'CommentsAPI' => ['addCommentForImage', 'deleteCommentForImage', 'toggleLikeCommentForImage',
                    'addCommentForNews', 'getCommentsForNews', 'deleteCommentForNews', 'toggleLikeCommentForNews'],
            ];

            $privateResources2 = [];
            foreach ($privateResources as $resource => $actions) {
                /*$actions2 = [];
                foreach($actions as $action)
                    $actions2[] = Phalcon\Text::camelize($action);*/
                $privateResources2[SupportClass::transformControllerName($resource)] = $actions;
            }

            $privateResources = $privateResources2;

            foreach ($privateResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            $moderatorsResources = [
                'users' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'tasksModer' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'logs' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'offers' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete',],
                'auctionsModer' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'admin/auctions' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete', 'enter', 'viewing', 'show', 'choice'],
                'categories' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'messages' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'categoriesAPI' => ['addCategory', 'editCategory', 'addSomeCategories'],
                'offersAPI' => ['addStatus'],
                'ReviewsAPI' => ['addType'],
                'ServicesAPI' => ['addImagesToAllServices'],
                'UserinfoAPI' => ['addUsers'],
            ];

            $moderatorsResources2 = [];
            foreach ($moderatorsResources as $resource => $actions) {
                /*$actions2 = [];
                foreach($actions as $action)
                    $actions2[] = Phalcon\Text::camelize($action);*/
                $moderatorsResources2[SupportClass::transformControllerName($resource)] = $actions;
            }
            $moderatorsResources = $moderatorsResources2;

            foreach ($moderatorsResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            //Public area resources
            //БД, все БД.
            $publicResources = [
                'index' => ['index', 'personcab'],
                'errors' => ['show401', 'show404', 'show500'],
                'session' => ['index', 'register', 'start', 'end', 'action'],
                'authorized' => ['index', 'register', 'start', 'end', 'action'],
                'auctions' => ['index'],
                'sessionAPI' => ['index', 'authWithSocial', 'end', 'getCurrentRole'],
                'CategoriesAPI' => ['index', 'getCategoriesForSite', 'getCategories'],
                'tenderAPI' => ['index'],
                'TasksAPI' => ['getTasksForSubject'],
                'ServicesAPI' => ['getServicesForSubject', 'getServices', 'incrementNumberOfDisplayForService',
                    'getServiceInfo'],
                'ReviewsAPI' => ['getReviewsForSubject', 'getReviewsForService'],
                'EventsAPI' => ['getEvents'],
                'TradePointsAPI' => ['getPointInfo'],
                'UserinfoAPI' => ['getUserinfo',],
                'CommentsAPI' => ['getCommentsForImage',],
                'CompaniesAPI' => ['getCompanyInfo'],
                'UserLocationAPI' => ['findUsers', 'getAutoCompleteForSearch', 'getUserById',
                    'getAutoCompleteForSearchServicesAndUsers', 'findUsersWithFilters'],
                'RegisterAPI' => ['index', 'deactivateLink', 'activateLink', 'getActivationCode',
                    'getResetPasswordCode', 'checkResetPasswordCode', 'changePassword', 'checkLogin'],
            ];

            $publicResources2 = [];
            foreach ($publicResources as $resource => $actions) {
                $publicResources2[SupportClass::transformControllerName($resource)] = $actions;
            }
            $publicResources = $publicResources2;
            foreach ($publicResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            $defectUserResources = [
                'UserinfoAPI' => ['index', 'settings', 'about', 'handler', 'restoreUser', 'deleteUser',
                    'setPhoto', 'editUserinfo', 'getUserInfo'],
                'RegisterAPI' => ['confirm', 'getActivationCode'],
            ];

            $defectUserResources2 = [];
            foreach ($defectUserResources as $resource => $actions) {
                $defectUserResources2[SupportClass::transformControllerName($resource)] = $actions;
            }
            $defectUserResources = $defectUserResources2;

            foreach ($defectUserResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }


            //Grant access to public areas to both users and guests
            foreach ($roles as $role) {
                foreach ($publicResources as $resource => $actions) {
                    foreach ($actions as $action) {
                        $acl->allow($role->getName(), $resource, $action);
                    }
                }
            }

            foreach ($defectUserResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('UserDefective', $resource, $action);
                    $acl->allow('User', $resource, $action);
                    $acl->allow('Moderator', $resource, $action);
                }
            }

            //Grant access to private area to role Users
            foreach ($privateResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('User', $resource, $action);
                    $acl->allow('Moderator', $resource, $action);
                }
            }

            foreach ($moderatorsResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('Moderator', $resource, $action);
                }
            }
            //The acl is stored in session, APC would be useful here too
            $this->persistent->acl = $acl;
        }

        return $this->persistent->acl;
    }

    public static function getTokenFromHeader()
    {
        if (!function_exists('getallheaders')) {
            function getallheaders()
            {
                if (!is_array($_SERVER)) {
                    return array();
                }

                $headers = array();
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                return $headers;
            }

        }

        $tokenRecieved = null;
        try {
            $result = getallheaders();
        } catch (Exception $e) {
        }

        if (isset(getallheaders()['Authorization']) || isset(getallheaders()['authorization'])) {
            $tokenRecieved = getallheaders()['Authorization']==null?getallheaders()['authorization']:getallheaders()['Authorization'];
        }

        if ($tokenRecieved == null)
            $tokenRecieved = "aaa";

        $dump = var_export(getallheaders(), true);

        SupportClass::writeMessageInLogFile('Headers '.$dump);

        return $tokenRecieved;
    }

    /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @returns bool
     */
    public function call(Micro $application)
    {
        return true;
    }
}



