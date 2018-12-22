<?php

use Phalcon\Db\Adapter\Pdo\Postgresql; 
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

use PhalconRest\Constants\Services;
use PhalconRest\Auth\Manager as AuthManager;
use App\Auth\UserEmailAccountType;

// Initializing a DI Container
$di = new \Phalcon\DI\FactoryDefault();



/**
 * Overriding Response-object to set the Content-type header globally
 */
$di->setShared(
        'response', function () {
    $response = new \Phalcon\Http\Response();
    $response->setContentType('application/json', 'utf-8');

    return $response;
}
);


/** Common config */
$di->setShared('config', $config);

/** Database */
$di->set(
        "db", function () use ($config) {
    return new Postgresql(
            [
        "host" => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->dbname,
            ]
    );
}
);

$di->setShared('logger', new FileAdapter('app/logs/debug.log'));

/** Service to perform operations */
$di->setShared('userService', '\App\Services\UserService');
$di->setShared('messageService', '\App\Services\MessageService');
$di->setShared('privateChatService', '\App\Services\PrivateChatService');
$di->setShared('chatHistoryService', '\App\Services\ChatHistoryService');

return $di;
