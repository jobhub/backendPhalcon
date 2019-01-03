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

    '\App\Controllers\SessionAPIController' => [
        'prefix' => '/authorization',
        'resources' => [
            /**
             * Авторизует пользователя в системе
             *
             * @method POST
             * @params login (это может быть его email или номер телефона), password
             * @return string json array [status, allForUser => [user, userinfo, settings], token, lifetime]
             */
            [
                'type' => 'post',
                'path' => '/login',
                'action' => 'indexAction'
            ],
        ]
    ],

    //********************************************************
    //********************************************************
    //CategoriesAPI
    //********************************************************
    //********************************************************
    '\App\Controllers\CategoriesAPIController' => [
        'prefix' => '/categories',
        'resources' => [
            /**
             * Возвращает категории в удобном для сайта виде
             *
             * @method GET
             *
             * @return string - json array с категориями
             */
            [
                'type' => 'get',
                'path' => '/get/site',
                'action' => 'getCategoriesForSiteAction'
            ],

            /**
             * Возвращает категории
             *
             * @method GET
             *
             * @return string - json array с категориями
             */
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getCategoriesAction'
            ],

            /**
             * Подписывает текущего пользователя на указанную категорию.
             * @access private
             * @method POST
             * @params category_id, radius
             * @return string - json array Status
             */
            [
                'type' => 'post',
                'path' => '/subscribe',
                'action' => 'setFavouriteAction'
            ],

            /**
             * Меняет радиус на получение уведомлений для подписки на категорию
             * @method PUT
             * @params radius, category_id
             * @return string - json array Status
             */
            [
                'type' => 'put',
                'path' => '/edit/radius',
                'action' => 'editRadiusInFavouriteAction'
            ],

            /**
             * Отписывает текущего пользователя от категории
             * @method DELETE
             * @param $category_id
             */
            [
                'type' => 'delete',
                'path' => '/unsubscribe/{category_id}',
                'action' => 'deleteFavouriteAction'
            ],

            /**
             * Возвращает все подписки пользователя на категории
             * @GET
             * @return string - json array - подписки пользователя
             */
            [
                'type' => 'get',
                'path' => '/get/favourites',
                'action' => 'getFavouritesAction'
            ],
        ]
    ],

    '\App\Controllers\UserinfoAPIController'=>[
        'prefix' => '/user/info',
        'resources' => [
            /**
             * Устанавливает одну из фотографий пользователя, как основную.
             * @access private
             * @method POST
             * @params image_id
             * @return Response - json array в формате Status.
             */
            [
                'type' => 'post',
                'path' => '/set-photo',
                'action' => 'setPhotoAction'
            ],

            /**
             * Возвращает публичные данные о пользователе.
             * Публичный метод.
             *
             * @method GET
             *
             * @param $user_id
             *
             * @return array [userinfo, [phones], [images], countNews, countSubscribers,
             *          countSubscriptions];
             */
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getUserInfoAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{user_id}',
                'action' => 'getUserInfoAction'
            ],

            /**
             * Меняет данные текущего пользоваателя.
             * Приватный метод.
             *
             * @method PUT
             *
             * @params first_name
             * @params last_name
             * @params patronymic
             * @params birthday
             * @params male
             * @params status
             * @params about
             * @params address
             *
             * @return string - json array - результат операции
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editUserInfoAction'
            ],

            /**
             * Добавляет все прикрепленные изображения к пользователю. Но суммарно изображений не больше 10.
             *
             * @access private
             *
             * @method POST
             *
             * @params (обязательно) изображения. Именование не важно.
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'post',
                'path' => '/add/images',
                'action' => 'addImagesAction'
            ],

            /**
             * Удаляет картинку из списка фотографий пользователя
             *
             * @method DELETE
             *
             * @param $image_id integer id изображения
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'delete',
                'path' => '/delete/image/{image_id}',
                'action' => 'deleteImageAction'
            ],
        ]
    ],

    '\App\Controllers\NewsAPIController'=>[
        'prefix' => '/news',
        'resources' => [
            /**
             * Возвращает новости для ленты текущего пользователя
             * Пока прростая логика с выводом только лишь новостей (без других объектов типа заказов, услуг)
             * @access private
             *
             * @method GET
             *
             * @return string - json array с новостями (или их отсутствием)
             */
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getNewsAction'
            ],
            /**
             * Возвращает все новости юзера и новости тех, на кого он подписан.
             * Пока прростая логика с выводом только лишь новостей (без других объектов типа заказов, услуг)
             *
             * @access private
             * @method GET
             *
             * @return string - json array с новостями (или их отсутствием)
             */
            [
                'type' => 'get',
                'path' => '/get/all',
                'action' => 'getAllNewsAction'
            ],

            /**
             * Создает новость компании или пользователя.
             * Если прикрепить изображения, они будут добавлены к новости.
             *
             * @access private
             *
             * @method POST
             *
             * @params int account_id (если не передать, то от имени аккаунта юзера по умолчанию)
             * @params string news_text
             * @params string title
             * @params файлы изображений.
             * @return string - json array объекта Status
             */
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addNewsAction'
            ],

            /**
             * Удаляет указанную новость
             *
             * @method DELETE
             *
             * @param $news_id
             *
             * @return string - json array объекта Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{news_id}',
                'action' => 'deleteNewsAction'
            ],

            /**
             * Редактирует новость.
             * Дата устанавливается текущая (на сервере).
             *
             * @method PUT
             *
             * @params int news_id, string news_text, title
             *
             * @return string - json array объекта Status
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editNewsAction'
            ],

            /**
             * Возвращает новости текущего пользователя/указанной компании пользователя.
             *
             * @method GET
             *
             * @param $companyId
             *
             * @return string - json array объектов news или Status, если ошибка
             */
            [
                'type' => 'get',
                'path' => '/get/own/{company_id}',
                'action' => 'getOwnNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/own',
                'action' => 'getOwnNewsAction'
            ],

            /**
             * Возвращает новости указанного объекта
             *
             * @method GET
             *
             * @param $id
             * @param $is_company (Можно не указывать, значение по умолчанию false)
             *
             * @return string - json array объектов news или Status, если ошибка
             */
            [
                'type' => 'get',
                'path' => '/get/by-subject/{id}/{is_company}',
                'action' => 'getSubjectsNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/by-subject/{id}',
                'action' => 'getSubjectsNewsAction'
            ],

            /**
             * Добавляет все прикрепленные изображения к новости. Но суммарно изображений не больше некоторого количества.
             *
             * @access private
             *
             * @method POST
             *
             * @params news_id
             * @params (обязательно) изображения. Именование не важно.
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'post',
                'path' => '/add/images',
                'action' => 'addImagesAction'
            ],

            /**
             * Удаляет картинку из списка изображений новости
             * @access private
             *
             * @method DELETE
             *
             * @param $image_id id изображения
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'delete',
                'path' => '/delete/image/{image_id}',
                'action' => 'deleteImageByIdAction'
            ],
        ]
    ],
];

return $routes;

