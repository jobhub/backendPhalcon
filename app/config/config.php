<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

/*
 * Статусы процесса выполнения задания
 */
define('STATUS_WAIT', 0);
define('STATUS_EXECUTING',1);
define('STATUS_REJECTED',2);
define('STATUS_EXECUTED',3);
define('STATUS_NON_EXECUTED',4);

/*
 * Статусы выполнения запросов
 */
define('STATUS_OK', 'OK');
define('STATUS_WRONG','WRONG_DATA');
define('STATUS_ALREADY_EXISTS','ALREADY_EXISTS');
define('STATUS_UNRESOLVED_ERROR','UNRESOLVED_ERROR');

/*
 * Роли
 */
define('ROLE_GUEST', 'Guests');
define('ROLE_USER', 'User');
define('ROLE_MODERATOR', 'Moderator');


define('API_URL', 'http://192.168.2.109/');

return new \Phalcon\Config([
    'database' => [
        'adapter'     => 'postgresql',
        'username'    => 'postgres',
        'password'    => '1234',
        'port'        => '5432',
        'host'        => 'localhost',
        'dbname'      => 'service_services',
    ],
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'APIDir' => APP_PATH . '/controllers/api/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'formsDir'     => APP_PATH . '/forms/',
        'cacheDir'       => BASE_PATH . '/cache/',

        // This allows the baseUri to be understand project paths that are not in the root directory
        // of the webpspace.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        'baseUri'        => preg_replace('/public([\/\\\\])index.php$/', '', $_SERVER["PHP_SELF"]),
    ]
]);
