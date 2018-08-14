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
                'userinfo' => ['index', 'edit', 'save', 'viewprofile', 'handler'],
                'userinfoAPI' => ['index', 'settings', 'about', 'handler', 'restoreUser', 'deleteUser',
                    'addPhoto'],

                'tenderAPI' => ['delete'],
                'reviews' => ['new', 'create'],

                'Companies' => ['index', 'end', 'new', 'edit', 'save', 'create', 'delete', 'search'],

                'CategoriesAPI' => ['getFavourites', 'setFavourite', 'deleteFavourite', 'editRadiusInFavourite'],
                'FavouriteUsersAPI' => ['setFavourite', 'deleteFavourite', 'getFavourites'],
                'NewsAPI' => ['getNews', 'addNew', 'deleteNew', 'editNew', 'getOwnNews', 'getSubjectNews'],
                'coordinationAPI' => ['addMessage', 'getMessages', 'selectOffer', 'addTokenId', 'clearTokens', 'finishTask', 'completeTask'],

                'CompaniesAPI' => ['addCompany', 'editCompany', 'deleteCompany', 'setManager', 'deleteManager',
                    'restoreCompany', 'setCompanyLogotype', 'deleteCompanyTestd'],

                'PhonesAPI' => ['addPhoneToCompany', 'addPhoneToTradePoint', 'deletePhoneFromCompany',
                    'deletePhoneFromTradePoint', 'editPhoneInTradePoint', 'editPhoneInCompany', 'test'],
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
                'RegisterAPI' => ['confirm']
            ];

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
                'categoriesAPI' => ['addCategory', 'editCategory'],
                'offersAPI' => ['addStatus'],
                'ReviewsAPI' => ['addType'],
            ];

            foreach ($moderatorsResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            //Public area resources
            //БД, все БД.
            $publicResources = [
                //   'base'       =>['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'userinfo' => ['viewprofile', 'handler'],
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

                'CompaniesAPI' => ['getCompanies',],
                'TasksAPI' => ['getTasksForSubject'],
                'ServicesAPI' => ['getServicesForSubject', 'getServices'],
                'Servicesapi' => ['getServicesForSubject', 'getServices'],
                'ReviewsAPI' => ['getReviews'],
            ];
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
        //$tokenRecieved = $this->request->getHeader("Authorization");
        $tokenRecieved = null;
        if (isset(getallheaders()['Authorization']))
            $tokenRecieved = getallheaders()['Authorization'];

        //if($_SERVER['METHOD'])
        if ($tokenRecieved == null)
            $tokenRecieved = "";

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

        $this->convertRequestBody();
        $auth = $this->session->get('auth');
        //Здесь будет логирование
        /*$log = new Logs();
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
        }*/

        if ($this->session->get("auth") != null) {
            $tokenRecieved = SecurityPlugin::getTokenFromHeader(); /*$this->getTokenFromResponce();*/
            $token = Accesstokens::findFirst(['userid = :userId: AND token = :token:',
                'bind' => ['userId' => $auth['id'],
                    'token' => hash('sha256', $tokenRecieved)]]);

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

        if (!$this->session->get('auth')) {
            $role = 'Guests';
        } else {
            $role = $auth['role'];
        }

        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        /*echo  $dispatcher->getControllerName()  .' ' . $dispatcher->getActionName();
        exit;*/

        $acl = $this->getAcl();

        if (!$acl->isResource($controller)) {
            $dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);

            /*$responce = new Response();

            $responce->setStatusCode('404', 'Not authorized');
            $responce->send();*/
            return false;
        }

        $allowed = $acl->isAllowed($role, $controller, $action);

        if (!$allowed) {
            $this->flash->error("Нет доступа.");
            $dispatcher->forward(['controller' => 'errors',
                'action' => 'show401']);
            /*$responce = new Response();
            $responce->setStatusCode('404', 'Not found');
            $responce->send();*/
            return false;
        }
    }

    private function convertRequestBody()
    {
        if ($this->request->getJsonRawBody() != null && $this->request->getJsonRawBody() != "") {
            $params = $this->request->getJsonRawBody();
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

}
