<?php

use App\Controllers\AbstractHttpException;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;
use Phalcon\Events\Manager;
use App\Middleware\CORSMiddleware; // for CORS origine
use App\Middleware\JWTMiddleware;

//error_reporting('E_ALL');
// define('BASE_PATH', dirname(__DIR__));
// define('APP_PATH', BASE_PATH . '/app');

try {

    // Loading Configs
    $config = require(__DIR__ . '/../app/config/config.php');

    // Autoloading classes
    require __DIR__ . '/../app/config/loader.php';

    // Initializing DI container
    /** @var \Phalcon\DI\FactoryDefault $di */
    $di = require __DIR__ . '/../app/config/di.php';

    // Initializing application
    $app = new \Phalcon\Mvc\Micro();
    // Setting DI container
    $app->setDI($di);


    $eventsManager = new Manager();
    $eventsManager->attach('micro', new CORSMiddleware());
    $eventsManager->attach('micro', new JWTMiddleware());
    $app->before(new CORSMiddleware());

    $app->before(new JWTMiddleware());

    // get jwt config
    $jwt_conf = require __DIR__ . '/../app/config/jwtConfig.php';
    /*// AUTH MICRO
    $auth = new AuthMicro($app, $jwt_conf);

    $auth->onUnauthorized(function($authMicro, $app) {
        $response = $app->response;
        $response->setStatusCode(401, 'Unauthorized');
        $response->setContentType("application/json");

        // to get the error messages
        $response->setContent(json_encode([$authMicro->getMessages()[0]]));
        $response->send();

        // return false to stop the execution
        return false;
    });*/

    // Setting up routing
    require __DIR__ . '/../app/config/router.php';

    // Making the correct answer after executing
    $app->after(
            function () use ($app) {
        // Getting the return value of method
        $return = $app->getReturnedValue();

        if (is_array($return)) {
            // Transforming arrays to JSON
            $app->response->setJsonContent($return);
        } elseif (!strlen($return)) {
            // Successful response without any content
            $app->response->setStatusCode('204', 'No Content');
        } else {
            // Unexpected response
            throw new Exception('Bad Response');
        }

        // Sending response to the client
        $app->response->send();
    }
    );

    /*if($app->request->isOptions()){

        $app->response->json('{"method":"OPTIONS"}', 200, $headers);
    }*/

    if($app->request->isOptions()){
        /*$headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];*/
        $response = new \Phalcon\Http\Response();
        $response->setStatusCode(200, 'All ok');

        $response->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader(
                'Access-Control-Allow-Methods',
                'GET, PUT, POST, DELETE, OPTIONS, CONNECT, HEAD, PURGE, PATCH'
            )
            ->setHeader(
                'Access-Control-Allow-Headers',
                'Content-Type, Authorization, X-Requested-With'
            )
            ->setHeader(
                'Access-Control-Max-Age',
                '86400'
            )
            ->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->send();
        return;
    }

    // Processing request
    $app->handle();
} catch (AbstractHttpException $e) {
    $response = $app->response;
    $response->setStatusCode($e->getCode(), $e->getMessage());
    $response->setJsonContent($e->getAppError());
    $response->send();
} catch (\Phalcon\Http\Request\Exception $e) {
    $app->response->setStatusCode(400, 'Bad request')
            ->setJsonContent([
                AbstractHttpException::KEY_CODE => 400,
                AbstractHttpException::KEY_MESSAGE => 'Bad request'
            ])
            ->send();
} catch (\Exception $e) {
    // Standard error format
    $result = [
        AbstractHttpException::KEY_CODE => 500,
        AbstractHttpException::KEY_MESSAGE => 'Some error occurred on the server.'
    ];

    // Sending error response
    $app->response->setStatusCode(500, 'Internal Server Error')
            ->setJsonContent($result)
            ->send();
}
