<?php

namespace App\Controllers;

use App\Models\Cities;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher;

use App\Models\Categories;
use App\Models\FavoriteCategories;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

//Services
use App\Services\CategoryService;

/**
 * Контроллер для работы с городами.
 * Содержит метод для получения всех городов с сервера
 *
 */
class CityController extends AbstractController
{
    /**
     * Возвращает города
     *
     * @method GET
     *
     */
    public function getCitiesAction()
    {
        return Cities::find()->toArray();
    }
}
