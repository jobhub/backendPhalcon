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
                'path' => '/messaging', //get all user discussions
                'action' => 'getUserPrivateChatAction'
            ],
            ['type' => 'get',
                'path' => '/messaging/spam', //get all spam user discussions
                'action' => 'getUserSpamPrivateChatAction'
            ],
            ['type' => 'get',
                'path' => '/messaging/hidden', //get all spam user discussions
                'action' => 'getUserHiddenPrivateChatAction'
            ]
        ]
    ],
    '\App\Controllers\PrivateChatController' => [
        'prefix' => '/user',
        'resources' => [
            ['type' => 'put',
                'path' => '/messaging/spam', //toggle hidden user discussions @param messaging_id
                'action' => 'spamTogglePrivateChatAction'
            ],
            ['type' => 'put',
                'path' => '/messaging/hidden', //toggle hidden user discussions @param messaging_id
                'action' => 'hiddenTogglePrivateChatAction'
            ]
        ]
    ],
    '\App\Controllers\GroupController' => [
        'prefix' => '/user/group',
        'resources' => [
            ['type' => 'post',
                'path' => '/new', //Create new group @param name
                'action' => 'newAction'
            ],
            ['type' => 'post',
                'path' => '/send', //send message to a chat history @param message_id, body, type,
                'action' => 'sendMessageAction'
            ],
            ['type' => 'post',
                'path' => '', //send message to a chat history @param message_id, body, type,
                'action' => 'mainAction'
            ],
            ['type' => 'post',
                'path' => '/messaging', //toggle hidden user discussions @param messaging_id
                'action' => 'messageAction'
            ],
            ['type' => 'get',
                'path' => '/messaging', //toggle hidden user discussions @param messaging_id
                'action' => 'messageAction'
            ]
        ]
    ],
    '\App\Controllers\ChannelController' => [
        'prefix' => '/user/channel',
        'resources' => [
            ['type' => 'post',
                'path' => '/new', //create new channel
                'action' => 'createChannelAction'
            ],
            ['type' => 'get',
                'path' => '/search{name}', //find channel
                'action' => 'findChannelAction'
            ],
            ['type' => 'post',
                'path' => '/subscribe', //find channel
                'action' => 'subscribePublicChannelAction'
            ],
            ['type' => 'post',
                'path' => '/send', //send message to channel
                'action' => 'sendMessageAction'
            ],
            ['type' => 'get',
                'path' => '/', //get user to channels
                'action' => 'getUserChannelAction'
            ],
            ['type' => 'get',
                'path' => '/messages{params}', //get channels messages
                'action' => 'getChannelMessageAction'
            ],
            ['type' => 'put',
                'path' => '/delete-history{params}', //get channels messages
                'action' => 'deleteHistoryAction'
            ],
            [
                'type' => 'post',
                'path' => '/add-user', // add users to channel @param json Array of users id
                'action' => 'addUsersToChannelAction'
            ],
            [
                'type' => 'post',
                'path' => '/delete-user', //remove users  to channels @param json Array of users id
                'action' => 'removeUserToChannelAction'
            ],
            [
                'type' => 'post',
                'path' => '/add-admin', //get channels messages @param json Array of users id
                'action' => 'adminChannelAction'
            ],
            [
                'type' => 'get',
                'path' => '/users{data}', //get channels messages @param channel_id
                'action' => 'getUsersOfChannelAction'
            ],
            [
                'type' => 'put',
                'path' => '/messaging/hidden', //toggle hidden user discussions @param messaging_id
                'action' => 'hiddenTogglePrivateChatAction'
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
    ],
    '\App\Controllers\RegisterAPIController' => [
        'prefix' => '/authorization',
        'resources' => [
    /**
     * Регистрирует пользователя в системе
     *
     * @access public
     * @method POST
     *
     * @params login, password,
     *
     * @return array. Если все прошло успешно - [status, token, lifetime (время, после которого токен будет недействительным)],
     * иначе [status,errors => <массив сообщений об ошибках>]
     */
    [
        'type' => 'post',
        'path' => '/register',
        'action' => 'indexAction'
    ],
    /**
     * Проверяет, подходит ли логин для регистрации нового пользователя
     *
     * @access public
     * @method POST
     *
     * @params login
     *
     * @return string json array Status
     */
    [
        'type' => 'post',
        'path' => '/check/login',
        'action' => 'checkLoginAction'
    ],
    /**
     * Активирует пользователя.
     *
     * @access defective
     *
     * @method POST
     *
     * @params (обязательные) firstname, lastname, male
     * @params (Необязательные) patronymic, birthday, about (много текста о себе),
     * @return string - json array Status
     */
    [
        'type' => 'post',
        'path' => '/confirm',
        'action' => 'confirmAction'
    ],
    /**
     * Подтверждает, что пользователь - владелец (пока только) почты.
     * Частично активирует пользователя, давая ему роль defectiveUser.
     * Или деактивирует ссылку, в зависимости от кода.
     *
     * @access publicfr
     *
     * @method POST
     *
     * @params activation_code, login
     *
     * @return Status
     */
    [
        'type' => 'post',
        'path' => '/activate',
        'action' => 'activateLinkAction'
    ],
    /**
     * Отправляет активационный код пользователю. Пока только на почту.
     * @access public, но пользователь должен быть авторизован
     * @method POST
     *
     * @return Response - json array в формате Status
     */
    [
        'type' => 'post',
        'path' => '/get/activationCode',
        'action' => 'getActivationCodeAction'
    ],
    /**
     * Отправляет пользователю код для сброса пароля
     * @access public
     *
     * @method POST
     *
     * @params login
     *
     * @return Status
     */
    [
        'type' => 'post',
        'path' => '/get/resetPasswordCode',
        'action' => 'getResetPasswordCodeAction'
    ],
    /**
     * Проверяет, верен ли код для сброса пароля
     * @access public
     *
     * @method POST
     *
     * @params login
     * @params reset_code
     *
     * @return Status
     */
    [
        'type' => 'post',
        'path' => '/check/resetPasswordCode',
        'action' => 'checkResetPasswordCodeAction'
    ],

    /**
     * Меняет пароль, если активационный код верен
     * @access public
     *
     * @method POST
     *
     * @params login
     * @params reset_code
     * @params password
     *
     * @return string - json array Status
     */
    [
        'type' => 'post',
        'path' => '/change/password',
        'action' => 'changePasswordAction'
    ],
]
        ],
    /**
     * Авторизует пользователя в системе
     *
     * @method POST
     * @params login (это может быть его email или номер телефона), password
     * @return string json array [status, allForUser => [user, userinfo, settings], token, lifetime]
     */
    '\App\Controllers\SessionAPIController' => [
        'prefix' => '/authorization',
        'resources' => [
            [
                'type' => 'post',
                'path' => '/login',
                'action' => 'indexAction'
            ],
        ]
    ],
];

return $routes;

