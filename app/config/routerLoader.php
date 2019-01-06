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
];

return $routes;

