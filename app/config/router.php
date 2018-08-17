<?php

$router = $di->getRouter();

// Define your routes here
$router->add("/:controller/:action/:params",
    array(
        "controller" => 1,
        "action" => 2,
        "params" => 3
    )
)->convert('controller', function($controller) {
    return SupportClass::transformControllerName($controller);
})/*->convert('action', function($action) {
    return Phalcon\Text::camelize($action);
})*/;
$router->add("/:controller",
    array(
        "controller" => 1,
        "action" => 'index',
    )
)->convert('controller', function($controller) {
    return SupportClass::transformControllerName($controller);
});
$router->add("/",
    array(
        "controller" => 'index',
        "action" => 'index',
    )
)->convert('controller', function($controller) {
    return SupportClass::transformControllerName($controller);
});
