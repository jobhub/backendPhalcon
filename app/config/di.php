<?php

use Phalcon\Db\Adapter\Pdo\Postgresql; 
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

use PhalconRest\Constants\Services;
use PhalconRest\Auth\Manager as AuthManager;
use App\Auth\UserEmailAccountType;
use App\Controllers\SessionAPIController;
use App\Services\SessionService;
use App\Libs\PseudoSession;
use Phalcon\Mailer;
use Phalcon\Di\FactoryDefault;

// Initializing a DI Container
$di = new FactoryDefault();
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

$di->setShared('authService', '\App\Services\AuthService');
$di->setShared('phoneService', '\App\Services\PhoneService');
$di->setShared('accountService', '\App\Services\AccountService');
$di->setShared('userInfoService', '\App\Services\UserInfoService');
$di->setShared('resetPasswordService', '\App\Services\ResetPasswordService');
$di->setShared('categoryService', '\App\Services\CategoryService');
$di->setShared('imageService', '\App\Services\ImageService');
$di->setShared('newsService', '\App\Services\NewsService');
$di->setShared('serviceService', '\App\Services\ServiceService');
$di->setShared('tagService', '\App\Services\TagService');
$di->setShared('pointService', '\App\Services\PointService');


$di['mailer'] = function() {
    $config = $this->getConfig()['mail'];
    $mailer = new \Phalcon\Mailer\Manager($config);
    return $mailer;
};

$di->setShared('session','\App\Libs\PseudoSession');

//It's only for parse content for sending mails.
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.volt' => function ($view) {
            $config = $this->getConfig();

            $volt = new VoltEngine($view, $this);

            $volt->setOptions([
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ]);

            return $volt;
        },
        '.phtml' => PhpEngine::class

    ]);
    return $view;
});

return $di;
