<?php

$routes = [
    '\App\Controllers\UserController' => [
        'prefix' => '/user',
        'resources' => [
            ['type' => 'post', // post; options, get, 
                'path' => '/login',
                'action' => 'loginAction'
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
    '\App\Controllers\RastreniyaController' => [
        'prefix' => '/user/rastreniya',
        'resources' => [
            ['type' => 'post',
                'path' => '/new', //Create new rastreniya @param content
                'action' => 'newAction'
            ],
            ['type' => 'post',
                'path' => '/comment', //add new rastreniya
                'action' => 'sendMessageAction'
            ],
            ['type' => 'get',
                'path' => '/page/{page}', //get Rastreniya
                'action' => 'getAction'
            ],
            ['type' => 'put',
                'path' => '/notice', //toggle hidden user discussions @param messaging_id
                'action' => 'noticeAction'
            ],
            ['type' => 'post',
                'path' => '/response', //toggle hidden user discussions @param messaging_id
                'action' => 'responseAction'
            ],
            ['type' => 'put',
                'path' => '', //toggle hidden user discussions @param messaging_id
                'action' => 'putAction'
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
             * @params (обязательные) first_name, last_name, male
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

            /**
             * Выдает текущую роль пользователя.
             * @access public
             * @method GET
             */
            [
                'type' => 'get',
                'path' => '/get/role',
                'action' => 'getCurrentRoleAction'
            ],

            /**
             * Возвращает аккаунты текущего пользователя
             *
             * @access private
             *
             * @method GET
             *
             * @return array
             */
            [
                'type' => 'get',
                'path' => '/get/accounts',
                'action' => 'getAccountsAction'
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
             * Удаляет текущего пользователя
             *
             * @method DELETE
             *
             * @return string - json array - объект Status - результат операции
             */
            [
                'type' => 'delete',
                'path' => '/delete',
                'action' => 'deleteUserAction'
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
            [
                'type' => 'get',
                'path' => '/get/{user_id}/{account_id}',
                'action' => 'getUserInfoAction'
            ],

            /**
             * Меняет данные текущего пользоваателя.
             * @access private.
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
             * @params image_text - текст к изображению
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

            [
                'type' => 'post',
                'path' => '/moderator/add-users',
                'action' => 'addUsersAction'
            ],

            /**
             * Возвращает результат поиска пользователей.
             * Публичный метод.
             *
             * @method POST
             *
             * @params string query
             * @params age_min - минимальный возраст
             * @params age_max - максимальный возраст
             * @params male - пол
             * @params has_photo - фильтр, имеется ли у него фотография
             * @params page - номер страницы
             * @params page_size - размер страницы
             *
             * @return array [userinfo, [phones], [images], countNews, countSubscribers,
             *          countSubscriptions];
             */
            [
                'type' => 'post',
                'path' => '/find',
                'action' => 'findUsersWithFiltersAction'
            ],
        ]
    ],

    '\App\Controllers\NewsAPIController'=>[
        'prefix' => '/news',
        'resources' => [
            /**
             * Возвращает новости для ленты текущего пользователя или указанного аккаунта (компании)
             *
             * @access private
             *
             * @param $account_id
             * @param $page
             * @param $page_size
             * @method GET
             *
             * @return string - json array с новостями (или их отсутствием)
             */
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{account_id}',
                'action' => 'getNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{account_id}/{page}',
                'action' => 'getNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{account_id}/{page}/{page_size}',
                'action' => 'getNewsAction'
            ],
            /**
             * Возвращает все новости юзера и новости тех, на кого он подписан.
             * Пока простая логика с выводом только лишь новостей (без других объектов типа заказов, услуг)
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
            [
                'type' => 'get',
                'path' => '/get/all/{page}',
                'action' => 'getAllNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/all/{page}/{page_size}',
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
             * @params string publish_date
             * @params string news_type
             *
             * @params array temp_images - массив с id временных изображений, которые должны быть добавлены в новость
             *
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
            [
                'type' => 'get',
                'path' => '/get/own/{company_id}/{page}',
                'action' => 'getOwnNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/own/{company_id}/{page}/{page_size}',
                'action' => 'getOwnNewsAction'
            ],

            /**
             * Возвращает новости указанного объекта
             *
             * @method GET
             * @param $page
             * @param $page_size
             * @param $id
             * @param $is_company (Можно не указывать, значение по умолчанию 0)
             * @param $account_id - аккаунт от имени которого совершаются действия.
             *
             * @return string - json array объектов news или Status, если ошибка
             */
            [
                'type' => 'get',
                'path' => '/get/by-subject/{id}',
                'action' => 'getSubjectsNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/by-subject/{id}/{is_company}',
                'action' => 'getSubjectsNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/by-subject/{id}/{is_company}/{account_id}',
                'action' => 'getSubjectsNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/by-subject/{id}/{is_company}/{account_id}/{page}',
                'action' => 'getSubjectsNewsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/by-subject/{id}/{is_company}/{account_id}/{page}/{page_size}',
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

    '\App\Controllers\ServicesAPIController'=>[
        'prefix' => '/service',
        'resources' => [
            /**
             * Возвращает все услуги заданной компании
             *
             * @method GET
             *
             * @param $id
             * @param $is_company
             * @param $page
             * @param $page_size
             * @param $account_id
             * @return string -  массив услуг в виде:
             *      [{serviceid, description, datepublication, pricemin, pricemax,
             *      regionid, name, rating, [Categories], [images (массив строк)] {TradePoint}, [Tags],
             *      {Userinfo или Company} }].
             */
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}/{is_company}',
                'action' => 'getServicesForSubjectAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}',
                'action' => 'getServicesForSubjectAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}/{is_company}/{account_id}',
                'action' => 'getServicesForSubjectAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}/{is_company}/{account_id}/{page}',
                'action' => 'getServicesForSubjectAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}/{is_company}/{account_id}/{page}/{page_size}',
                'action' => 'getServicesForSubjectAction'
            ],

            /**
             * Возвращает все услуги данного юзера (или его компании).
             *
             * @method GET
             *
             * @param $company_id - если не указан, то будут возвращены услуги текущего пользователя.
             *        Иначе компании, в которой он должен быть хотя бы менеджером.
             *
             * @return string -  массив услуг в виде:
             *      [{serviceid, description, datepublication, pricemin, pricemax,
             *      regionid, name, rating, [Categories], [images (массив строк)] {TradePoint}, [Tags],
             *      {Userinfo или Company} }].
             */
            [
                'type' => 'get',
                'path' => '/get/own',
                'action' => 'getOwnServicesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/own/{company_id}',
                'action' => 'getOwnServicesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/own/{company_id}/{page}',
                'action' => 'getOwnServicesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/own/{company_id}/{page}/{page_size}',
                'action' => 'getOwnServicesAction'
            ],

            /**
             * Возвращает услуги. Во-первых, принимает тип запроса в параметре type_query:
             * 0 - принимает строку user_query, центральную точку для поиска - center => [longitude => ..., latitude =>  ...],
             * крайнюю точку для определения радиуса - diagonal => [longitude => ..., latitude =>  ...],
             * массив регионов (id-шников) (regions_id). возвращает
             * список услуг и всего им соответствующего;
             * 1 - запрос на получение элементов интеллектуального поиска. Принимает те же данные, что и в 0-вом запросе.
             * Возвращает массив с типом элемента (строкой - 'company', 'service' и 'category'), id элемента и его названием для отображения в строке
             *  ([{type : ..., id : ..., name : ...}, {...}]);
             * 2 - еще один запрос на получение услуг. Принимает id элемента и тип строкой (type), как отдавалось в запрос 1.
             * Возвращает массив услуг, как в 0-вом запросе.
             * 3 - запрос на получение услуг по категориям. Принимает массив категорий categoriesId, центральную и крайнюю точку
             * и массив регионов, как в 0-вом запросе. Возвращает массив услуг, как везде.
             * 4 - запрос для поиска по области. Центральная точка, крайняя точка, массив регионов, которые попадут в область.
             * Возвращает массив услуг, как везде.
             * 5 - запрос для поиска с фильтрами. Принимает центральную, диагональные точки, массив категорий,
             * минимальную и максимальную цены (price_min, price_max) и минимальный рейтинг (rating_min)
             *
             * @access public
             *
             * @method POST
             *
             * @params int type_query (обязательно)
             * @params array center (необязательно) [longitude, latiitude]
             * @params array diagonal (необязательно) [longitude, latiitude]
             * @params string type (необязательно) 'company', 'service', 'category'.
             * @params int id (необязательно)
             * @params string user_query (необязательно)
             * @params array regions_id (необязательно) массив регионов,
             * @params array categories_id (необязательно) массив категорий,
             * @params price_min
             * @params price_max
             * @params rating_min
             *
             * @return string json массив [status, service, company/user_info,[categories],[trade_points],[images]] или
             *   json массив [status, [{type : ..., id : ..., name : ...}, {...}]].
             */
            [
                'type' => 'post',
                'path' => '/get',
                'action' => 'getServicesAction'
            ],

            /**
             * Удаляет картинку из списка картинок услуги
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

            /**
             * Удаляет указанную услугу
             * @access private
             *
             * @method DELETE
             *
             * @param $service_id
             * @return Response - с json массивом в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{service_id}',
                'action' => 'deleteServiceAction'
            ],

            /**
             * Редактирует указанную услугу
             * @access private
             *
             * @method PUT
             *
             * @params service_id
             * @params description
             * @params name
             * @params price_min, price_max (или же вместо них просто price)
             * @params region_id
             * @params deleted_tags - массив int-ов - id удаленных тегов
             * @params added_tags - массив строк
             * @return Response - с json массивом в формате Status
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editServiceAction'
            ],

            /**
             * Добавляет новую услугу к субъекту. Если не указана компания, можно добавить категории.
             *
             * @method POST
             *
             * @params (необязательные) массив old_points - массив id tradePoint-ов,
             * (необязательные) массив new_points - массив объектов TradePoints
             * @params (необязательные) account_id, description, name, price_min, price_max (или же вместо них просто price)
             *           (обязательно) region_id,
             *           (необязательно) longitude, latitude
             *           (необязательно) если не указана компания, можно указать id категорий в массиве categories.
             * @params массив строк tags с тегами.
             * @params прикрепленные изображения. Именование роли не играет.
             *
             * @return string - json array. Если все успешно - [status, service_id], иначе [status, errors => <массив ошибок>].
             */
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addServiceAction'
            ],

            /**
             * Добавляет картинки к услуге
             *
             * @method POST
             *
             * @params (обязательно) service_id
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'post',
                'path' => '/add/images',
                'action' => 'addImagesAction'
            ],

            /**
             * Связывает услугу с точкой оказания услуг
             *
             * @method POST
             *
             * @params (обязательные) service_id, point_id
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'post',
                'path' => '/link/point',
                'action' => 'linkServiceWithPointAction'
            ],

            /**
             * Убирает связь услуги и точки оказания услуг
             *
             * @method DELETE
             *
             * @param $service_id
             * @param $point_id
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'delete',
                'path' => '/unlink/point/{service_id}/{point_id}',
                'action' => 'unlinkServiceAndPointAction'
            ],

            /**
             * Увеличивает на 1 счетчик числа просмотров услуги.
             * @method PUT
             * @params service_id
             * @return string - json array в формате Status
             */
            [
                'type' => 'put',
                'path' => '/increment/display',
                'action' => 'incrementNumberOfDisplayForServiceAction'
            ],

            /**
             * Возвращает все заказы, которые могут быть связаны с данной услугой.
             * На самом деле нет, конечно же. Логики того, как это будет делаться нет.
             *
             * @method GET
             *
             * @param $service_id
             * @return string - json array tasks
             */
            [
                'type' => 'get',
                'path' => '/get/tasks-for-service/{service_id}',
                'action' => 'getTasksForService'
            ],

            /**
             * Возвращает публичную информацию об услуге.
             * Публичный доступ.
             *
             * @method GET
             *
             * @param $service_id
             *
             * @return string - json array {status, service, [points => {point, [phones]}], reviews (до двух)}
             */
            [
                'type' => 'get',
                'path' => '/get/info/{service_id}',
                'action' => 'getServiceInfoAction'
            ],

            /**
             * Подписывает текущего пользователя или его аккаунт (компанию) на услугу
             *
             * @method POST
             *
             * @params service_id
             * @params account_id = null
             *
             * @return Response с json ответом в формате Status
             */
            /*[
                'type' => 'post',
                'path' => '/add/favourite',
                'action' => 'setFavouriteAction'
            ],*/

            /**
             * Отменяет подписку на услугу
             *
             * @method DELETE
             *
             * @param $service_id
             * @param $account_id = null
             *
             * @return Response с json ответом в формате Status
             */
            /*[
                'type' => 'delete',
                'path' => '/delete/favourite/{service_id}',
                'action' => 'deleteFavouriteAction'
            ],
            [
                'type' => 'delete',
                'path' => '/delete/favourite/{service_id}/{account_id}',
                'action' => 'deleteFavouriteAction'
            ],*/

            /**
             * Возвращает избранные услуги пользователя
             *
             * @method GET
             *
             * @param $account_id = null
             * @param $page = 1
             * @param $page_size = Services::DEFAULT_RESULT_PER_PAGE
             *
             * @return string - json array с подписками (просто id-шники)
             */
            /*[
                'type' => 'get',
                'path' => '/get/favourites',
                'action' => 'getFavouritesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/favourites/{account_id}',
                'action' => 'getFavouritesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/favourites/{account_id}/{page}',
                'action' => 'getFavouritesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/favourites/{account_id}/{page}/{page_size}',
                'action' => 'getFavouritesAction'
            ],*/
        ]
    ],

    '\App\Controllers\CompaniesAPIController'=>[
        'prefix' => '/company',
        'resources' => [
            /**
             * Возвращает компании текущего пользователя
             *
             * @param $with_points
             *
             * @method GET
             * @return array - json array компаний
             */
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getCompaniesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{with_points}',
                'action' => 'getCompaniesAction'
            ],

            /**
             * Создает компанию.
             *
             * @method POST
             * @params (Обязательные)name, full_name
             * @params (необязательные) tin, region_id, website, email, description
             * @return int company_id
             */
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addCompanyAction'
            ],

            /**
             * Удаляет указанную компанию
             * @method DELETE
             *
             * @param $company_id
             * @return string - json array Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{company_id}',
                'action' => 'deleteCompanyAction'
            ],

            /**
             * Восстанавливает компанию
             *
             * @method POST
             *
             * @params company_id
             *
             * @return string - данные компании
             */
            [
                'type' => 'post',
                'path' => '/restore',
                'action' => 'restoreCompanyAction'
            ],

            /**
             * Редактирует данные компании
             * @method PUT
             * @params company_id, name, full_name, tin, region_id, website, email, description
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editCompanyAction'
            ],

            /**
             * Устанавливает логотип для компании. Сам логотип должен быть передан в файлах. ($_FILES)
             * @method POST
             * @params company_id
             * @return Response
             */
            [
                'type' => 'post',
                'path' => '/set/logotype',
                'action' => 'setCompanyLogotypeAction'
            ],

            /**
             * Делает указанного пользователя менеджером компании
             *
             * @method POST
             *
             * @params user_id, company_id
             *
             * @return int account_id
             */
            [
                'type' => 'post',
                'path' => '/add/manager',
                'action' => 'setManagerAction'
            ],

            /**
             * Удаляет пользователя из менеджеров компании
             *
             * @method DELETE
             *
             * @param $user_id
             * @param $company_id
             *
             * @return string message. Just message.
             */
            [
                'type' => 'delete',
                'path' => '/delete/manager/{company_id}/{user_id}',
                'action' => 'deleteManagerAction'
            ],

            /**
             * Возвращает публичную информацию о компании.
             * Публичный доступ
             *
             * @method GET
             *
             * @param $company_id
             * @return string - json array компаний
             */
            [
                'type' => 'get',
                'path' => '/get/info/{company_id}',
                'action' => 'getCompanyInfoAction'
            ],
        ]
    ],

    '\App\Controllers\TradePointsAPIController'=>[
        'prefix' => '/trade-point',
        'resources' => [
            /**
             * Возвращает точки предоставления услуг для пользователя или для указанной компании пользователя.
             * @access private
             * @method GET
             * @param integer $company_id
             * @return string - json array of [status, [TradePoint, phones]], если все успешно,
             * или json array в формате Status в ином случае
             */
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getPointsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{company_id}',
                'action' => 'getPointsAction'
            ],

            /**
             * Возвращает точки предоставления услуг назначенные текущему пользователю
             * @access private
             * @method GET
             * @param  int $manager_user_id
             * @return string - json array of [TradePoint, phones]
             */
            [
                'type' => 'get',
                'path' => '/get/for-manager',
                'action' => 'getPointsForUserManagerAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-manager/{manager_user_id}',
                'action' => 'getPointsForUserManagerAction'
            ],

            /**
             * Добавляет точку оказания услуг к компании
             * @access private
             * @method POST
             *
             * @params (Обязательные)   string name, double latitude, double longitude
             * @params (Необязательные) string email, string website, string address, string fax, int account_id
             * @params (Необязательные) (int manager_user_id, int company_id) - парой
             * @return array с point_id
             */
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addTradePointAction'
            ],

            /**
             * Редактирует указанную точку оказания услуг
             *
             * @method PUT
             *
             * @param (Обязательные)   int point_id string name, double latitude, double longitude,
             *        (Необязательные) string email, string website, string address, string fax, string time, int manager_user_id.
             *
             * @return Phalcon\Http\Response с json массивом в формате Status
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editTradePointAction'
            ],

            /**
             * Удаляет указанную точку оказания услуг
             *
             * @method DELETE
             *
             * @param (Обязательные) $point_id
             * @return Phalcon\Http\Response с json массивом в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{point_id}',
                'action' => 'deleteTradePointAction'
            ],

            /**
             * Возвращает публичную информацию об указанной точке оказания услуг.
             * Публичный доступ.
             *
             * @access public
             * @method GET
             *
             * @param $point_id
             * @return array - {point,[services]}
             */
            [
                'type' => 'get',
                'path' => '/get/info/{point_id}',
                'action' => 'getPointInfoAction'
            ],


            [
                'type' => 'post',
                'path' => '/moderator/change-to-markers',
                'action' => 'changePointsAction'
            ],
        ]
    ],

    '\App\Controllers\CommentsAPIController'=>[
        'prefix' => '/comment',
        /**
         * Возможные значения для $type:
         *      image-user
         *      news
         *      service
         */
        'resources' => [
            /**
             * Возвращает комментарии к указанной фотографии пользователя
             *
             * @method GET
             *
             * @param $image_id
             *
             * @return string - json array массив комментариев
             */
            /*[
                'type' => 'get',
                'path' => '/images-users/get/{image_id}',
                'action' => 'getCommentsForImageAction'
            ],*/

            /**
             * Возвращает комментарии к указанной новости
             *
             * @method GET
             *
             * @param $news_id
             *
             * @return string - json array массив комментариев
             */
            /*[
                'type' => 'get',
                'path' => '/news/get/{news_id}',
                'action' => 'getCommentsForNewsAction'
            ],*/

            /**
             * Добавляет комментарий к фотографии пользователя
             * @access private
             *
             * @method POST
             *
             * @params object_id - id изображения
             * @params comment_text - текст комментария
             * @params reply_id (не обязательное) - id комментария, на который оставляется ответ.
             * @params account_id (не обязательное) - если не указано, значит от имени пользователя - аккаунта по умолчанию.
             *
             * @return string - json array в формате Status + id созданного комментария
             */
            /*[
                'type' => 'post',
                'path' => '/images-users/add',
                'action' => 'addCommentForImageAction'
            ],*/

            /**
             * Удаляет комментарий, оставленный к фотографии пользователя
             *
             * @method DELETE
             *
             * @param $comment_id int id комментария
             *
             * @return string - json array в формате Status - результат операции
             */
            /*[
                'type' => 'delete',
                'path' => '/images-users/delete/{comment_id}',
                'action' => 'deleteCommentForImageAction'
            ],*/

            /**
             * Добавляет комментарий к новости
             * @access private
             *
             * @method POST
             *
             * @params object_id - id новости
             * @params comment_text - текст комментария
             * @params account_id - int id аккаунта, от имени которого добавляется комментарий.ыфя
             * Если не указан, то от имени текущего пользователя по умолчанию.
             *
             * @return string - json array в формате Status - результат операции
             */
            /*[
                'type' => 'post',
                'path' => '/news/add',
                'action' => 'addCommentForNewsAction'
            ],*/

            /**
             * Удаляет комментарий, оставленный к фотографии пользователя
             *
             * @method DELETE
             *
             * @param $comment_id int id комментария
             *
             * @return string - json array в формате Status - результат операции
             */
            /*[
                'type' => 'delete',
                'path' => '/news/delete/{comment_id}',
                'action' => 'deleteCommentForNewsAction'
            ],*/

            /**
             * Меняет лайкнутость текущим пользователем указанного комментария.
             *
             * @method POST
             *
             * @params comment_id - int id комментария
             * @params account_id - int id аккаунта, от имени которого совершается данное действие
             * (если не указан, значит берется по умолчанию для пользователя)
             *
             * @return Response
             */
            /*[
                'type' => 'post',
                'path' => '/images-users/like/toggle',
                'action' => 'toggleLikeCommentForImageAction'
            ],*/

            /**
             * Меняет лайкнутость текущим пользователем указанного комментария.
             *
             * @method POST
             *
             * @params comment_id - int id комментария
             * @params account_id - int id аккаунта, от имени которого совершается данное действие
             * (если не указан, значит берется по умолчанию для пользователя)
             *
             * @return Response
             */
            /*[
                'type' => 'post',
                'path' => '/news/like/toggle',
                'action' => 'toggleLikeCommentForNewsAction'
            ],*/


            /**
             * Добавляет комментарий к указанному объекту
             * @access private
             *
             * @method POST
             *
             * @param $type
             *
             * @params object_id - id новости
             * @params comment_text - текст комментария
             * @params account_id - int id аккаунта, от имени которого добавляется комментарий.
             * Если не указан, то от имени текущего пользователя по умолчанию.
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'post',
                'path' => '/add/{type}',
                'action' => 'addCommentAction'
            ],

            /**
             * Удаляет комментарий указаннного типа
             *
             * @method DELETE
             *
             * @param $type string - тип комментария
             * @param $comment_id int id комментария
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'delete',
                'path' => '/delete/{type}/{comment_id}',
                'action' => 'deleteCommentAction'
            ],

            /**
             * Возвращает комментарии к указанному объекту
             *
             * @method GET
             * @param $type
             * @param $object_id
             * @param $parent_id
             * @param $account_id
             * @param $page
             * @param $page_size
             * @return string - json array массив комментариев
             */
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}',
                'action' => 'getCommentsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}/{parent_id}',
                'action' => 'getCommentsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}/{parent_id}/{account_id}',
                'action' => 'getCommentsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}/{parent_id}/{account_id}/{page}',
                'action' => 'getCommentsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}/{parent_id}/{account_id}/{page}/{page_size}',
                'action' => 'getCommentsAction'
            ],
        ]
    ],

    '\App\Controllers\PhonesAPIController'=>[
        'prefix' => '',
        'resources' => [
            /**
             * Добавляет телефон для указанной компании
             * @method POST
             * @params integer company_id, string phone или integer phone_id
             * @return Phalcon\Http\Response с json ответом в формате Status;
             */
            [
                'type' => 'post',
                'path' => '/company/add/phone',
                'action' => 'addPhoneToCompanyAction'
            ],

            /**
             * Добавляет телефон для указанной точки оказания услуг
             * @method POST
             * @params integer point_id, string phone или integer phone_id
             * @return Phalcon\Http\Response с json ответом в формате Status;
             */
            [
                'type' => 'post',
                'path' => '/trade-point/add/phone',
                'action' => 'addPhoneToTradePointAction'
            ],

            /**
             * Убирает телефон из списка телефонов компании
             *
             * @method DELETE
             *
             * @param int $phone_id
             * @param int $company_id
             * @return Phalcon\Http\Response с json массивом в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/company/delete/phone/{phone_id}/{company_id}',
                'action' => 'deletePhoneFromCompanyAction'
            ],

            /**
             * Убирает телефон из списка телефонов точки
             *
             * @method DELETE
             *
             * @param int $phone_id
             * @param int $point_id
             * @return Phalcon\Http\Response с json массивом в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/trade-point/delete/phone/{phone_id}/{point_id}',
                'action' => 'deletePhoneFromTradePointAction'
            ],

            /**
             * Изменяет определенный номер телефона у определенной точки услуг
             * @method PUT
             * @params integer point_id, string phone (новый) и integer phone_id (старый)
             * @return Phalcon\Http\Response с json ответом в формате Status;
             */
            [
                'type' => 'put',
                'path' => '/trade-point/edit/phone',
                'action' => 'editPhoneInTradePointAction'
            ],

            /**
             * Изменяет определенный номер телефона у определенной компании
             * @method PUT
             * @params integer company_id, string phone (новый) и integer phone_id (старый)
             * @return Phalcon\Http\Response с json ответом в формате Status;
             */
            [
                'type' => 'put',
                'path' => '/company/edit/phone',
                'action' => 'editPhoneInCompanyAction'
            ],

            /**
             * Добавляет телефон пользователю.
             * Приватный метод.
             *
             * @method POST
             *
             * @params string phone или integer phone_id
             * @return Phalcon\Http\Response с json ответом в формате Status;
             */
            [
                'type' => 'post',
                'path' => '/user/info/add/phone',
                'action' => 'addPhoneToUserAction'
            ],

            /**
             * Убирает телефон из списка телефонов пользователя.
             * Приватный метод.
             *
             * @method DELETE
             *
             * @param int $phone_id
             * @return Phalcon\Http\Response с json массивом в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/user/info/delete/phone/{phone_id}',
                'action' => 'deletePhoneFromUserAction'
            ],
        ]
    ],

    '\App\Controllers\FavouriteCompaniesAPIController'=>[
        'prefix' => '/user/info',
        'resources' => [
            /**
             * Подписывает текущего пользователя на компанию
             *
             * @method POST
             *
             * @params company_id
             *
             * @return Response с json ответом в формате Status
             */
            [
                'type' => 'post',
                'path' => '/subscribe-to/company',
                'action' => 'setFavouriteAction'
            ],

            /**
             * Отменяет подписку на компанию
             *
             * @method DELETE
             *
             * @param $company_id
             *
             * @return Response с json ответом в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/unsubscribe-from/company/{company_id}',
                'action' => 'deleteFavouriteAction'
            ],

            /**
             * Возвращает подписки пользователя на компании
             *
             * @return string - json array с подписками (просто id-шники)
             */
            [
                'type' => 'get',
                'path' => '/get/favourite/companies',
                'action' => 'getFavouritesAction'
            ],
        ]
    ],

    '\App\Controllers\FavouriteUsersAPIController'=>[
        'prefix' => '/user/info',
        'resources' => [
            /**
             * Подписывает текущего пользователя на указанного
             * @method POST
             * @params user_id
             * @return string - json array Status
             */
            [
                'type' => 'post',
                'path' => '/subscribe-to/user',
                'action' => 'setFavouriteAction'
            ],

            /**
             * Отменяет подписку на пользователя
             * @method
             * @param $user_id
             * @return string - json array Status
             */
            [
                'type' => 'delete',
                'path' => '/unsubscribe-from/user/{user_id}',
                'action' => 'deleteFavouriteAction'
            ],

            /**
             * Возвращает подписки текущего пользователя
             * @method GET
             * @return string - json array подписок
             */
            [
                'type' => 'get',
                'path' => '/get/favourite/users',
                'action' => 'getFavouritesAction'
            ],
        ]
    ],

    '\App\Controllers\RequestsAPIController'=>[
        'prefix' => '/request',
        'resources' => [
            /**
             * Добавляет запрос на получение услуги
             *
             * @method POST
             *
             * @params service_id, description, date_end.
             * @params (необязательный) account_id
             * @return Response с json массивом в формате Status
             */
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addRequestAction'
            ],

            /**
             * Удаляет заявку
             *
             * @method DELETE
             *
             * @param $request_id
             * @return Response с json массивом в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{request_id}',
                'action' => 'deleteRequestAction'
            ],

            /**
             * Редактирует заявку
             *
             * @method PUT
             *
             * @params request_id, description, date_end
             * @return Response с json массивом в формате Status
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editRequestAction'
            ],

            /**
             * Редактирует заявку
             *
             * @method GET
             *
             * @param $company_id (необязательный)
             * @return string - json массив с объектами Requests и Status-ом
             */
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getRequestsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{company_id}',
                'action' => 'getRequestsAction'
            ],
        ]
    ],

    '\App\Controllers\TasksAPIController'=>[
        'prefix' => '/task',
        'resources' => [
            /**
             * Добавляет заказ
             *
             * @method POST
             *
             * @params (обязательные) category_id, name, price, date_end.
             * @params (необязательные) account_id, description, deadline, polygon, region_id, longitude, latitude.
             *
             * @return string - json array  формате Status
             */
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addTaskAction'
            ],

            /**
             * Возвращает все задания субъекта (для него самого)
             *
             * @method GET
             *
             * @param $company_id
             *
             * @return string - массив заданий (Tasks) и Status
             *
             */
            [
                'type' => 'get',
                'path' => '/get/own',
                'action' => 'getTasksForCurrentUserAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/own/{company_id}',
                'action' => 'getTasksForCurrentUserAction'
            ],

            /**
             * Возвращает все задания указанного субъекта
             *
             * @method GET
             *
             * @param $id
             * @param $is_company
             *
             * @return string - массив заданий (Tasks)
             */
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}',
                'action' => 'getTasksForSubjectAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}/{is_company}',
                'action' => 'getTasksForSubjectAction'
            ],

            /**
             * Удаление заказа
             *
             * @method DELETE
             * @param $task_id
             * @return string - json array в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{task_id}',
                'action' => 'deleteTaskAction'
            ],

            /**
             * Редактирование задания
             *
             * @method PUT
             * @params (обязательные) task_id.
             * @params (необязательные)  description, deadline, polygon,
             *                           region_id, longitude, latitude,
             *                           category_id, name, price, date_end.
             * @return string - json array в формате Status
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editTaskAction'
            ],

            /**
             * Выбирает предложение для выполнения заказа
             *
             * @method POST
             * @params offer_id
             * @return string - json array в формате Status
             */
            [
                'type' => 'post',
                'path' => '/select-offer',
                'action' => 'selectOfferAction'
            ],
        ]
    ],

    '\App\Controllers\OffersAPIController'=>[
        'prefix' => '/offer',
        'resources' => [
            /**
             * Возвращает предложения для определенного задания
             * @method GET
             * @param $task_id
             *
             * @return string - json array объектов Offers
             */
            [
                'type' => 'get',
                'path' => '/get/for-task/{task_id}',
                'action' => 'getForTaskAction'
            ],

            /**
             * Добавляет предложение на выполнение указанного задания
             *
             * @method POST
             *
             * @params (Обязательные) task_id, deadline, price.
             * @params (Необязательные) description, account_id.
             *
             * @return string - json array в формате Status
             */
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addOfferAction'
            ],

            /**
             * Возвращает офферсы субъекта
             *
             * @method GET
             *
             * @param $company_id (необязательный). Если не отправить, будут возвращены для текущего пользователя
             *
             * @return string - json array объектов Offers
             */
            [
                'type' => 'get',
                'path' => '/get/for-current',
                'action' => 'getForCurrentUserAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-current/{company_id}',
                'action' => 'getForCurrentUserAction'
            ],

            /**
             * Удаляет предложение на выполнение заявки
             *
             * @method DELETE
             * @param $offer_id
             *
             * @return string - json array в формате Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{offer_id}',
                'action' => 'deleteOfferAction'
            ],

            /**
             * Редактирует предложение на выполнение указанного задания
             *
             * @method PUT
             *
             * @params (Обязательные) offer_id, deadline, price.
             * @params (Необязательные) description.
             *
             * @return string - json array в формате Status
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editOfferAction'
            ],
        ]
    ],

    '\App\Controllers\ReviewsAPIController'=>[
        'prefix' => '/review',
        'resources' => [
            /**
             * Добавляет отзыв.
             *
             * @method POST
             *
             * @params int binder_id, int binder_type, bool executor, int rating, string text_review
             *
             * @return Response - Status
             */
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addReviewAction'
            ],

            /**
             * Редактирует отзыв.
             *
             * @method PUT
             *
             * @params int rating, review_id
             * @param (Необязатальные) review_text.
             *
             * @return Response - Status
             */
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editReviewAction'
            ],

            /**
             * Удаляет отзыв.
             *
             * @method DELETE
             *
             * @param $review_id
             *
             * @return Response - Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{review_id}',
                'action' => 'deleteReviewAction'
            ],

            /**
             * Возвращает отзывы об указанном субъекте, будь то пользователь или компания.
             *
             * @method GET
             *
             * @param $id - id субъекта
             * @param $is_company - тип субъекта
             *
             * @return string - json array [status,[reviews]]
             */
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}',
                'action' => 'getReviewsForSubjectAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}/{is_company}',
                'action' => 'getReviewsForSubjectAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}/{is_company}/{page}',
                'action' => 'getReviewsForSubjectAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-subject/{id}/{is_company}/{page}/{page_size}',
                'action' => 'getReviewsForSubjectAction'
            ],

            /**
             * Возвращает отзывы, связанные с указанной услугой.
             *
             * @method GET
             *
             * @param $serviceId - id услуги
             * @param $numPage - номер страницы
             * @param $widthPage - размер страницы
             *
             * @return string - json array [status,reviews => [review,{userinfo or company}]]
             */
            [
                'type' => 'get',
                'path' => '/get/for-service/{service_id}',
                'action' => 'getReviewsForServiceAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-service/{service_id}/{page}',
                'action' => 'getReviewsForServiceAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/for-service/{service_id}/{page}/{page_size}',
                'action' => 'getReviewsForServiceAction'
            ],

            /**
             * Добавляет все прикрепленные изображения к отзыву. Но суммарно изображений для отзыва не больше 3.
             *
             * @access private
             *
             * @method POST
             *
             * @params (обязательно) review_id
             * @params (обязательно) изображения. Именование не важно.
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'post',
                'path' => '/add/images',
                'action' => 'addImagesAction'
            ],
        ]
    ],

    '\App\Controllers\ImageController'=>[
        'prefix' => '/image',
        'resources' => [
            /**
             * Возвращает изображения для указанного объекта.
             * Тип может быть:
             *      user
             *      news
             *      review
             *      service
             *      company (пока еще не реализовано)
             *      temp
             *
             * @access private
             *
             * @method GET
             * @param $type string
             * @param $object_id int
             * @param $page
             * @param $page_size
             * @param $account_id
             * @params (обязательно) изображения. Именование не важно.
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}',
                'action' => 'getImagesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}/{account_id}',
                'action' => 'getImagesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}/{account_id}/{page}',
                'action' => 'getImagesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/{type}/{object_id}/{account_id}/{page}/{page_size}',
                'action' => 'getImagesAction'
            ],

            /**
             * Добавляет все прикрепленные изображения к указанному объекту.
             *
             * @access private
             *
             * @method POST
             *
             * @param $type;
             *
             * @params object_id
             *
             * @params image_text в случае изображения пользователя
             *
             * @params (обязательно) изображения.
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'post',
                'path' => '/add/{type}',
                'action' => 'addImagesAction'
            ],

            /**
             * Удаляет картинку из списка изображений
             * @access private
             *
             * @method DELETE
             *
             * @param $type
             * @params (посылается в теле запроса) $image_id id изображения или же массив id-шников изображений
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'delete',
                'path' => '/delete/{type}',
                'action' => 'deleteImageByIdAction'
            ],
        ]
    ],

    '\App\Controllers\LikeController'=>[

        /**
         * Возможные значения $type
         *      'comment-image-user';
         *      'comment-news';
         *      'comment-service';
         *      'news';
         *      'service';
         *      'image-user';
         */

        'prefix' => '/like',
        'resources' => [

            /**
             * Меняет лайкнутость текущим пользователем указанного объекта
             *
             * @method POST
             *
             * @param $type
             * @params object_id - int id комментария
             * @params account_id - int id аккаунта, от имени которого совершается данное действие
             * (если не указан, значит берется по умолчанию для пользователя)
             *
             * @return Response
             */
            [
                'type' => 'post',
                'path' => '/toggle/{type}',
                'action' => 'toggleLikeAction'
            ],

        ]
    ],

    '\App\Controllers\ForwardsController'=>[
        'prefix' => '/forward',

        /**
         * Возможные значения $type
         *      'news';
         *      'service';
         *      'image-user';
         */

        'resources' => [
            /**
             * Создает репост
             * @access private
             *
             * @method POST
             *
             * @param $type
             *
             * @params object_id - id объекта
             * @params forward_text - текст репоста
             * @params account_id - int id аккаунта
             * Если не указан, то от имени текущего пользователя по умолчанию.
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'post',
                'path' => '/add/{type}',
                'action' => 'addForwardAction'
            ],

            /**
             * Удаляет репост
             *
             * @method DELETE
             *
             * @param $type string - тип репоста
             * @param $object_id int id объекта
             * @param $account_id int id аккаунта
             *
             * @return string - json array в формате Status - результат операции
             */
            [
                'type' => 'delete',
                'path' => '/delete/{type}/{object_id}',
                'action' => 'deleteForwardAction'
            ],
            [
                'type' => 'delete',
                'path' => '/delete/{type}/{object_id}/{account_id}',
                'action' => 'deleteForwardAction'
            ],
        ]
    ],

    '\App\Controllers\FavouriteController'=>[
        'prefix' => '/favourite',

        /**
         * Возможные значения $type
         *      'user';
         *      'service';
         *      'company';
         */

        'resources' => [
            /**
             * Подписывает текущего пользователя на что-либо
             * @method POST
             *
             * @param $type
             *
             * @params account_id = null
             * @params object_id = null
             *
             * @return string - json array Status
             */
            [
                'type' => 'post',
                'path' => '/add/{type}',
                'action' => 'setFavouriteAction'
            ],

            /**
             * Отменяет подписку
             * @method
             * @param $object_id
             * @param $type
             * @param $account_id = null
             * @return string - json array Status
             */
            [
                'type' => 'delete',
                'path' => '/delete/{type}/{object_id}',
                'action' => 'deleteFavouriteAction'
            ],
            [
                'type' => 'delete',
                'path' => '/delete/{type}/{object_id}/{account_id}',
                'action' => 'deleteFavouriteAction'
            ],

            /**
             * Возвращает всех подписчиков на текущий аккаунт (а именно, компанию или пользователя)
             *
             * @method GET
             *
             * @param $account_id = null
             * @param $query = null
             * @param $page = 1
             * @param $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE
             *
             * @return string - json array подписок
             */
            [
                'type' => 'get',
                'path' => '/get/subscribers',
                'action' => 'getSubscribersAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/subscribers/{account_id}',
                'action' => 'getSubscribersAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/subscribers/{account_id}/{query}',
                'action' => 'getSubscribersAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/subscribers/{account_id}/{query}/{page}',
                'action' => 'getSubscribersAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/subscribers/{account_id}/{query}/{page}/{page_size}',
                'action' => 'getSubscribersAction'
            ],

            /**
             * Возвращает все подписки текущего аккаунта (или всех аккаунтов компании, если аккаунт с ней связан)
             *
             * @method GET
             *
             * @param $account_id = null
             * @param $query = null
             * @param $page = 1
             * @param $page_size = FavouriteModel::DEFAULT_RESULT_PER_PAGE
             *
             * @return string - json array подписок
             */
            [
                'type' => 'get',
                'path' => '/get/subscriptions',
                'action' => 'getSubscriptionsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/subscriptions/{account_id}',
                'action' => 'getSubscriptionsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/subscriptions/{account_id}/{query}',
                'action' => 'getSubscriptionsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/subscriptions/{account_id}/{query}/{page}',
                'action' => 'getSubscriptionsAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/subscriptions/{account_id}/{query}/{page}/{page_size}',
                'action' => 'getSubscriptionsAction'
            ],

            /**
             * Возвращает избранные услуги пользователя
             *
             * @method GET
             *
             * @param $account_id = null
             * @param $page = 1
             * @param $page_size = Services::DEFAULT_RESULT_PER_PAGE
             *
             * @return string - json array с подписками (просто id-шники)
             */
            [
                'type' => 'get',
                'path' => '/get/service',
                'action' => 'getFavouriteServicesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/service/{account_id}',
                'action' => 'getFavouriteServicesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/service/{account_id}/{page}',
                'action' => 'getFavouriteServicesAction'
            ],
            [
                'type' => 'get',
                'path' => '/get/service/{account_id}/{page}/{page_size}',
                'action' => 'getFavouriteServicesAction'
            ],
        ]
    ],

    '\App\Controllers\UserLocationAPIController'=>[
        'prefix' => '/user/location',
        'resources' => [
            /**
             * Устанавливает текущее местоположение текущего пользователя.
             *
             * @access private.
             *
             * @method POST
             * @params latitude;
             * @params longitude;
             * @return string - json array результат операции.
             */
            [
                'type' => 'post',
                'path' => '/set',
                'action' => 'setLocationAction'
            ],

            /**
             * Ищет пользователей по поисковой строке и внутри заданных координат.
             * @access public
             *
             * @method POST
             *
             * @params string query
             * @params center - [longitude => ..., latitude => ...] - центральная точка
             * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
             * @return string - json array - массив пользователей.
             *          [status, users=>[userid, email, phone, firstname, lastname, patronymic,
             *          longitude, latitude, lasttime,male, birthday,pathtophoto]]
             */
            [
                'type' => 'post',
                'path' => '/find',
                'action' => 'findUsersAction'
            ],

            /**
             * Ищет пользователей по поисковой строке и внутри заданных координат.
             * С заданным фильтром.
             * @access public
             *
             * @method POST
             *
             * @params string query
             * @params center - [longitude => ..., latitude => ...] - центральная точка
             * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
             * @params age_min - минимальный возраст
             * @params age_max - максимальный возраст
             * @params male - пол
             * @params has_photo - фильтр, имеется ли у него фотография
             * @return string - json array - массив пользователей.
             *          [status, users=>[userid, email, phone, firstname, lastname, patronymic,
             *          longitude, latitude, lasttime,male, birthday,pathtophoto]]
             */
            [
                'type' => 'post',
                'path' => '/find/with-filters',
                'action' => 'findUsersWithFiltersAction'
            ],

            /**
             * Возвращает данные для автокомплита поиска по пользователям.
             *
             * @access public
             *
             * @method POST
             *
             * @params string query
             * @params center - [longitude => ..., latitude => ...] - центральная точка
             * @params diagonal - [longitude => ..., latitude => ...] - диагональная точка (обязательно правая верхняя)
             * @return string - json array - массив пользователей.
             *          [status, users=>[userid, firstname, lastname, patronymic,status]]
             */
            [
                'type' => 'post',
                'path' => '/get/autocomplete',
                'action' => 'getAutoCompleteForSearchAction'
            ],

            /**
             * Возвращает данные по id пользователя аналогичные поиску, но без поиска.
             *
             * @access public
             *
             * @method GET
             *
             * @param int $user_id
             * @return string - json array - массив пользователей.
             *          [status, users=>[userid, email, phone, firstname, lastname, patronymic,
             *          longitude, latitude, lasttime, male, birthday, pathtophoto,status]]
             */
            [
                'type' => 'get',
                'path' => '/get/info/{user_id}',
                'action' => 'getUserByIdAction'
            ],
        ]
    ],

    '\App\Controllers\TestController'=>[
        'prefix' => '/moderator/test',
        'resources' => [
            [
                'type' => 'post',
                'path' => '/add-users',
                'action' => 'addUsersAction'
            ],

            [
                'type' => 'post',
                'path' => '/add-accounts',
                'action' => 'addAccountsAction'
            ],

            [
                'type' => 'post',
                'path' => '/add-news',
                'action' => 'addNewsAction'
            ],

            [
                'type' => 'post',
                'path' => '/add-likes/{offset}/{count}',
                'action' => 'addLikesAction'
            ],

            [
                'type' => 'post',
                'path' => '/add-array-like/{news_id}/{user_id}',
                'action' => 'addArrayLikeAction'
            ],

            [
                'type' => 'get',
                'path' => '/get-array-liked/{news_id}/{user_id}',
                'action' => 'getLikedArrayAction'
            ],

            [
                'type' => 'get',
                'path' => '/get-table-liked/{news_id}/{user_id}',
                'action' => 'getLikedTableAction'
            ],

            [
                'type' => 'get',
                'path' => '/get-array-liked-cycle/{news_limit}/{users_limit}',
                'action' => 'getLikedArrayInCycleAction'
            ],
            [
                'type' => 'get',
                'path' => '/get-array-liked-cycle/{news_limit}/{users_limit}/{offset}',
                'action' => 'getLikedArrayInCycleAction'
            ],

            [
                'type' => 'get',
                'path' => '/get-table-liked-cycle/{news_limit}/{users_limit}',
                'action' => 'getLikedTableInCycleAction'
            ],
            [
                'type' => 'get',
                'path' => '/get-table-liked-cycle/{news_limit}/{users_limit}/{offset}',
                'action' => 'getLikedTableInCycleAction'
            ],


            [
                'type' => 'post',
                'path' => '/send-message-to-email',
                'action' => 'sendMessageAction'
            ],
        ]
    ],
];

return $routes;

