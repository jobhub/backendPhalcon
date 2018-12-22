<?php

$routes = [
    '\App\Controllers\UserController' => [
        'prefix' => '/user',
        'resources' => [
            ['type' => 'get', // post; options, get, 
                'path' => '/list/{id}',
                'action' => 'getUserListAction'
            ],
            ['type' => 'get', // post; options, get, 
                'path' => '/find/{email}',
                'action' => 'getUserByAction'
            ], 
            ['type' => 'get', // post; options, get, 
                'path' => '/chanels/{idUser}',
                'action' => 'getUserChanelAction'
            ]
        ]
    ],
    '\App\Controllers\MessageController' => [
        'prefix' => '/chat',
        'resources' => [
            ['type' => 'post', 
                'path' => '/send',
                'action' => 'sendMessageAction'
            ],
            ['type' => 'post',  
                'path' => '/chat-box',
                'action' => 'getChatBoxAction'
            ], 
            ['type' => 'post', 
                'path' => '/all-readed',
                'action' => 'setAllToReadAction'
            ]
        ]
    ],
    '\App\Controllers\GroupController' => [
        'prefix' => '/group',
        'resources' => [
            [
                'type' => 'post', 
                'path' => '/new-group',
                'action' => 'addAction'
            ] 
        ]
    ]
];

return $routes;

