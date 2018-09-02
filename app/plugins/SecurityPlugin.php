<?php

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Http\Response;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class SecurityPlugin extends Plugin
{
    /**
     * Returns an existing or new access control list
     *
     * @returns AclList
     */
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
                'coordination' => ['index', 'end', 'new', 'edit', 'save', 'create', 'delete'],
                'tasks' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete', 'mytasks', 'doingtasks', 'workingtasks', 'editing'],
                'auctions' => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete', 'enter', 'viewing', 'show', 'choice'],
                'offers' => ['index', 'new', 'create', 'myoffers', 'editing', 'saving', 'deleting', 'search'],
                'Userinfo' => ['index', 'edit', 'save', 'viewprofile', 'handler'],
                'UserinfoAPI' => ['index', 'settings', 'about', 'handler', 'restoreUser', 'deleteUser',
                    'setPhoto','editUserinfo'],

                'tenderAPI' => ['delete'],
                'reviews' => ['new', 'create'],

                'Companies' => ['index', 'end', 'new', 'edit', 'save', 'create', 'delete', 'search'],

                'CategoriesAPI' => ['getFavourites', 'setFavourite', 'deleteFavourite', 'editRadiusInFavourite'],
                'FavouriteUsersAPI' => ['setFavourite', 'deleteFavourite', 'getFavourites'],
                'NewsAPI' => ['getNews', 'addNew', 'deleteNew', 'editNew', 'getOwnNews', 'getSubjectNews'],
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
                    'rejectRequest', 'editImageService', 'addImages', 'deleteImage'],
                'RequestsAPI' => ['addRequest', 'deleteRequest', 'editRequest', 'getRequests', 'cancelRequest',
                    'confirmPerformanceRequest'],

                'TasksAPI' => ['addTask', 'deleteTask', 'editTask', 'getTasksForCurrentUser', 'selectOffer', 'cancelTask',
                    'confirmPerformanceTask'],
                'OffersAPI' => ['getForTask', 'addOffer', 'getForSubject', 'deleteOffer', 'editOffer', 'getForTask',
                    'confirmOffer', 'rejectOffer', 'performTask'],

                'ReviewsAPI' => ['addReview', 'editReview', 'deleteReview'],
                'RegisterAPI' => ['confirm'],
                'EventsAPI' => ['addEvent', 'setImage', 'deleteEvent', 'editEvent'],
                'UserLocationAPI' => ['setLocation'],
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
                //   'base'       =>['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'Userinfo' => ['viewprofile', 'handler'],
                'index' => ['index'],
                'register' => ['index'],
                'errors' => ['show401', 'show404', 'show500'],
                'session' => ['index', 'register', 'start', 'end', 'action'],
                'authorized' => ['index', 'register', 'start', 'end', 'action'],
                'auctions' => ['index'],
                'registerAPI' => ['index'],
                'sessionAPI' => ['index', 'authWithSocial', 'end'],
                'CategoriesAPI' => ['index', 'getCategoriesForSite'],
                'tenderAPI' => ['index'],
                'TasksAPI' => ['getTasksForSubject'],
                'ServicesAPI' => ['getServicesForSubject', 'getServices', 'incrementNumberOfDisplayForService',
                    'getServiceInfo'],
                'ReviewsAPI' => ['getReviewsForSubject', 'getReviewsForService'],
                'EventsAPI' => ['getEvents'],
                'TradePointsAPI' => ['getPointInfo'],
                'Search' => ['index'],
                'UserinfoAPI' => ['getUserinfo'],
                'CompaniesAPI' => ['getCompanyInfo']
            ];

            $publicResources2 = [];
            foreach ($publicResources as $resource => $actions) {
                /*$actions2 = [];
                foreach($actions as $action)
                    $actions2[] = Phalcon\Text::camelize($action);*/
                $publicResources2[SupportClass::transformControllerName($resource)] = $actions;
            }
            $publicResources = $publicResources2;

            foreach ($publicResources as $resource => $actions) {
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
            //SupportClass::writeMessageInLogFile('Функции getallheaders не существует, пытаюсь создать другую');
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

            // SupportClass::writeMessageInLogFile('Типо инициализировал функцию getallheaders');
        }

        //$tokenRecieved = $this->request->getHeader("Authorization");
        $tokenRecieved = null;
        // SupportClass::writeMessageInLogFile('Попытался запустить функцию getallheaders');
        try {
            $result = getallheaders();
        } catch (Exception $e) {
            // SupportClass::writeMessageInLogFile('Получил Exception: ' . $e->getMessage());
        }
        //SupportClass::writeMessageInLogFile('getheaders[Authorization] = ' . getallheaders()['Authorization']);
        //SupportClass::writeMessageInLogFile('getheaders[Authorization] = ' . getallheaders()['authorization']);

        if (isset(getallheaders()['Authorization'])) {
            //SupportClass::writeMessageInLogFile('Перед вызовом getallheaders с получением токена авторизации');
            $tokenRecieved = getallheaders()['Authorization'];
            //SupportClass::writeMessageInLogFile('После вызовом getallheaders с получением токена авторизации');
        }

        //if($_SERVER['METHOD'])
        if ($tokenRecieved == null)
            $tokenRecieved = "aaa";
        //SupportClass::writeMessageInLogFile('Токен из заголовка = ' . $tokenRecieved);
        return $tokenRecieved;
    }

    public function getTokenFromResponce()
    {
        if ($this->request->isPost() || $this->request->isGet())
            $tokenRecieved = $this->request->getPost("authorization");

        if ($tokenRecieved == null) {
            $tokenRecieved = $this->request->getJsonRawBody();
            if ($tokenRecieved != null) {
                $tokenRecieved = $tokenRecieved['authorization'];
            }
        }
        return $tokenRecieved;
    }

    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return bool
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $response = new Response();
        /*SupportClass::writeMessageInLogFile('В securityPlugin при вызове ' . $dispatcher->getControllerName()
            . '/' . $dispatcher->getActionName());*/
        $this->convertRequestBody();
        $auth = $this->session->get('auth');
        //Здесь будет логирование
        $log = new Logs();
        if ($this->session->get("auth") != null) {
            $auth = $this->session->get("auth");
            $log->setUserId($auth['id']);
        }

        $log->setController($dispatcher->getControllerName());
        $log->setAction($dispatcher->getActionName());
        $log->setDate(date('Y-m-d H:i'));

        if ($log->save() == false) {
            foreach ($log->getMessages() as $message) {
                $this->flash->error((string)$message);
            }
        }
        if (!$this->notAPIController($dispatcher->getControllerName())) {
            if ($this->session->get("auth") != null) {
                //SupportClass::writeMessageInLogFile('В security: Перед получением токена из заголовка');
                $tokenRecieved = SecurityPlugin::getTokenFromHeader(); /*$this->getTokenFromResponce();*/
                //SupportClass::writeMessageInLogFile('В security: Перед получением токена из БД');
                //SupportClass::writeMessageInLogFile('В security: id юзера в сессии = ' . $auth['id']);
                //SupportClass::writeMessageInLogFile('В security: хэш токена = ' . hash('sha256', $tokenRecieved));
                $token = Accesstokens::findFirst(['userid = :userId: AND token = :token:',
                    'bind' => ['userId' => $auth['id'],
                        'token' => hash('sha256', $tokenRecieved)]]);

                //SupportClass::writeMessageInLogFile('В security: После получения токена из БД - '. $token->getToken());
                if (!$token) {
                    $this->session->remove('auth');
                    $this->session->destroy();
                } else {
                    if (strtotime($token->getLifetime()) <= time()) {
                        $this->session->remove('auth');
                        $this->session->destroy();
                        $token->delete();
                    }
                }
            }
        }

        if (!$this->session->get('auth')) {
            $role = 'Guests';
        } else {
            $role = $auth['role'];
        }

        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        $acl = $this->getAcl();

        if (!$acl->isResource($controller)) {
            $dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return false;
        }

        $allowed = $acl->isAllowed($role, $controller, $action);

        if (!$allowed) {
            $this->flash->error("Нет доступа.");
            $dispatcher->forward(['controller' => 'errors',
                'action' => 'show401']);
            return false;
        }
    }

    private function convertRequestBody()
    {
        if ($this->request->getJsonRawBody() != null && $this->request->getJsonRawBody() != "") {
            $params = $this->request->getRawBody();
            $params = json_decode($params,true);
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
        if ($controllerName == 'index' || $controllerName == 'errors') {
            return true;
        }
        return false;
    }
}
