<?php

use Phalcon\Db\Adapter\Pdo\Postgresql; 
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

use PhalconRest\Constants\Services;
use PhalconRest\Auth\Manager as AuthManager;
use App\Auth\UserEmailAccountType;
use App\Controllers\SessionAPIController;
use App\Services\SessionService;
use App\Libs\PseudoSession;
use Phalcon\Mailer;

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

$di->setShared('logger', new FileAdapter(BASE_PATH.'/app/logs/debug.log'));

/** Service to perform operations */
$di->setShared('userService', '\App\Services\UserService');
$di->setShared('messageService', '\App\Services\MessageService');
$di->setShared('privateChatService', '\App\Services\PrivateChatService');
$di->setShared('chatHistoryService', '\App\Services\ChatHistoryService');

//
$di->setShared(
    "PhonesAPI",
    function () {
        $phonesAPI = new PhonesAPIController();

        return $phonesAPI;
    }
);

$di->setShared('sessionAPI','\App\Services\SessionService');

$di->setShared(
    "TradePointsAPI",
    function () {
        $tradePointsAPI = new TradePointsAPIController();

        return $tradePointsAPI;
    }
);

$di->setShared(
    "CompaniesAPI",
    function () {
        $companiesAPI = new CompaniesAPIController();

        return $companiesAPI;
    }
);

$di->setShared(
    "ContactDetailsCompanyCompanyAPI",
    function () {
        $contactDetailsAPI = new ContactDetailsCompanyAPIController();

        return $contactDetailsAPI;
    }
);

$di['mailer'] = function() {
    $config = $this->getConfig()['mail'];
    $mailer = new \Phalcon\Mailer\Manager($config);
    return $mailer;
};

$di->setShared('session','\App\Libs\PseudoSession');

return $di;
