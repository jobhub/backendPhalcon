<?php

use Phalcon\Mvc\Micro;
use Phalcon\Events\Manager; 
use App\Middleware\CORSMiddleware; // for CORS origine

$router = $di->getRouter();
$eventsManager = new Manager();

$app = new Micro();

 
$eventsManager->attach('micro', new CORSMiddleware()); 
$app->before(new CORSMiddleware());

//$eventsManager->attach('micro', new CORSMiddleware());
//$app->after(new CORSMiddleware());

// Handler user
$usersCollection = new \Phalcon\Mvc\Micro\Collection();
$usersCollection->setHandler('\App\Controllers\UserController', true);
$usersCollection->setPrefix('/user');
$usersCollection->post('/add', 'addAction');
$usersCollection->get('/list/{id}', 'getUserListAction');
$usersCollection->get('/find/{email}', 'getUserByAction'); 
$usersCollection->get('/chanels/{idUser}', 'getUserChanelAction'); 

// Handler Message
$msgCollection = new \Phalcon\Mvc\Micro\Collection();
$msgCollection->setHandler('\App\Controllers\MessageController', true);
$msgCollection->setPrefix('/chat');
$msgCollection->post('/send', 'sendMessageAction'); 
$msgCollection->Options('/send', 'sendMessageAction'); 
$msgCollection->post('/chat-box', 'getChatBoxAction'); 
$msgCollection->options('/chat-box', 'getChatBoxAction');
$msgCollection->post('/all-readed', 'setAllToReadAction'); 
$msgCollection->options('/all-readed', 'setAllToReadAction'); 

// Handler Group
$groupCollection = new \Phalcon\Mvc\Micro\Collection();
$groupCollection->setHandler('\App\Controllers\GroupController', true);
$groupCollection->setPrefix('/chat');
$groupCollection->post('/new-group', 'addAction');  


$app->mount($groupCollection);
$app->mount($usersCollection);
$app->mount($msgCollection); 
// not found URLs
$app->notFound(
  function () use ($app) {
      $exception =
        new \App\Controllers\HttpExceptions\Http404Exception(
          _('URI not found or error in request.'),
          \App\Controllers\AbstractController::ERROR_NOT_FOUND,
          new \Exception('URI not found: ' . $app->request->getMethod() . ' ' . $app->request->getURI())
        );
      throw $exception;
  }
);

$router->handle();
$app->setEventsManager($eventsManager);