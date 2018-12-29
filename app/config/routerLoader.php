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

