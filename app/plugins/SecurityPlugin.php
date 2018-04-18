<?php

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;

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
				'user'  => new Role(
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
                'coordination'      => ['index', 'end', 'new', 'edit', 'save', 'create', 'delete'],

                'tasks'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete','mytasks','doingtasks', 'workingtasks','editing'],

                'auctions'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete','enter','viewing','show','choice'],
                'offers'      => ['index',  'new', 'create', 'myoffers','editing','saving','deleting','search'],
                'userinfo'   =>['index', 'edit', 'save','viewprofile'],
                'userinfoAPI' => ['index', 'edit']
			];

			foreach ($privateResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}

			$moderatorsResources = [
                'users'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'tasksModer'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'logs'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'offers'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'auctionsModer'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'admin/auctions'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete','enter','viewing','show','choice'],
                'categories'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'messages'      => ['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
            ];

            foreach ($moderatorsResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

			//Public area resources
            //БД, все БД.
			$publicResources = [
             //   'base'       =>['index', 'search', 'new', 'edit', 'save', 'create', 'delete'],
                'userinfo'   =>['viewprofile'],
				'index'      => ['index'],
				'register'   => ['index'],
				'errors'     => ['show401', 'show404', 'show500'],
				'session'    => ['index', 'register', 'start', 'end', 'action'],
                'authorized'    => ['index', 'register', 'start', 'end', 'action'],
                'auctions'      => ['index'],
                'registerAPI'      => ['index'],
                'sessionAPI'      => ['index'],
			];
			foreach ($publicResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}

			//Grant access to public areas to both users and guests
			foreach ($roles as $role) {
				foreach ($publicResources as $resource => $actions) {
					foreach ($actions as $action){
						$acl->allow($role->getName(), $resource, $action);
					}
				}
			}

			//Grant access to private area to role Users
			foreach ($privateResources as $resource => $actions) {
				foreach ($actions as $action){
					$acl->allow('User', $resource, $action);
                    $acl->allow('Moderator', $resource, $action);
				}
			}

            foreach ($moderatorsResources as $resource => $actions) {
                foreach ($actions as $action){
                    $acl->allow('Moderator', $resource, $action);
                }
            }
			//The acl is stored in session, APC would be useful here too
			$this->persistent->acl = $acl;
		}

		return $this->persistent->acl;
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

		if (!$auth){
			$role = 'Guests';
		} else {
			$role = $auth['role'];
		}

		//$role = $auth['role'];
        //$role = 'Guests';
		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();

		$acl = $this->getAcl();

		if (!$acl->isResource($controller)) {
			$dispatcher->forward([
				'controller' => 'errors',
				'action'     => 'show404'
			]);

			return false;
		}

		$allowed = $acl->isAllowed($role, $controller, $action);
		if (!$allowed) {
		    $this->flash->error("Нет доступа.");
			$dispatcher->forward([
				'controller' => 'errors',
				'action'     => 'show401'
			]);
            return false;
		}

	}
}
