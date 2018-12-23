<?php

$routes = [
    '\App\Controllers\UserController' => [
        'prefix' => '/user', 
        'resources' => [ 
            ['type' => 'post', // post; options, get, 
            'path' => '/login',
                'action' => 'loginAction'
                ],
        ]
    ],
    '\App\Controllers\RegisterAPIController' => [
        'prefix' => '/authorization',
        'resources' => [
            [
                'type' => 'post',
                'path' => '/register',
                'action' => 'indexAction'
            ]
        ]
    ]
];

return $routes;

