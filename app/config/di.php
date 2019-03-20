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
use App\Libs\Database\MySQLAdapter;

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

$di->setShared(
    "mysql", function () use ($config) {
    $mysql = new MySQLAdapter($config['mysql_server']);
    return $mysql;
}
);

$di->setShared('logger', new FileAdapter(BASE_PATH.'/app/logs/debug.log'));

//
$di->setShared(
    "PhonesAPI",
    function () {
        $phonesAPI = new PhonesAPIController();

        return $phonesAPI;
    }
);

$di->setShared('accountService', '\App\Services\AccountService'); //1
$di->setShared('authService', '\App\Services\AuthService'); //2
$di->setShared('resetPasswordService', '\App\Services\ResetPasswordService'); //3
$di->setShared('userInfoService', '\App\Services\UserInfoService'); //4
$di->setShared('likeService', '\App\Services\LikeService');  //5
$di->setShared('categoryService', '\App\Services\CategoryService'); //6
$di->setShared('imageService', '\App\Services\ImageService'); //7
$di->setShared('newsService', '\App\Services\NewsService'); //8
$di->setShared('serviceService', '\App\Services\ServiceService'); //9
$di->setShared('tagService', '\App\Services\TagService'); //10
$di->setShared('pointService', '\App\Services\PointService'); //11
$di->setShared('phoneService', '\App\Services\PhoneService'); //12
$di->setShared('companyService', '\App\Services\CompanyService'); //13
$di->setShared('commentService', '\App\Services\CommentService'); //14
$di->setShared('userLocationService', '\App\Services\UserLocationService'); //15
$di->setShared('requestService', '\App\Services\RequestService'); //16
$di->setShared('taskService', '\App\Services\TaskService'); //17
$di->setShared('offerService', '\App\Services\OfferService'); //18
$di->setShared('reviewService', '\App\Services\ReviewService'); //19
$di->setShared('forwardService', '\App\Services\ForwardService'); //20
$di->setShared('favouriteService', '\App\Services\FavouriteService'); //21
$di->setShared('markerService', '\App\Services\MarkerService'); //22

$di->setShared('channelService', '\App\Services\ChannelService'); //23
$di->setShared('groupService', '\App\Services\GroupService'); //24
$di->setShared('rastreniyaService', '\App\Services\RastreniyaService'); //25

/** Service to perform operations */
$di->setShared('userService', '\App\Services\UserService'); //26
$di->setShared('messageService', '\App\Services\MessageService'); //27
$di->setShared('privateChatService', '\App\Services\PrivateChatService'); //28
$di->setShared('chatHistoryService', '\App\Services\ChatHistoryService'); //29

$di->setShared('socialNetService', '\App\Services\SocialNetService'); //30
$di->setShared('confirmService', '\App\Services\ConfirmService'); //31
$di->setShared('cityService', '\App\Services\CityService'); //32
$di->setShared('inviteService', '\App\Services\InviteService'); //33
$di->setShared('notificationService', '\App\Services\NotificationService'); //34
$di->setShared('commonService', '\App\Services\CommonService'); //35
$di->setShared('productService', '\App\Services\ProductService'); //36
$di->setShared('linkService', '\App\Services\LinkService'); //37
$di->setShared('settingsService', '\App\Services\SettingsService'); //38
$di->setShared('eventService', '\App\Services\eventService'); //39

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

$di->setShared('SMS', function () use ($di) {
    return new SMSFactory\Sender($di);
});

return $di;
