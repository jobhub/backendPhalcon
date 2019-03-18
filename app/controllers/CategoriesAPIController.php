<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http404Exception;
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
 * Контроллер для работы с категориями.
 * Здесь методы для получения категорий и для работы с подписками
 * пользователей на категории.
 *
 */
class CategoriesAPIController extends AbstractController
{
    /**
     * Возвращает категории
     *
     * @method GET
     *
     * @param $type
     *
     * @return array
     */
    public function getCategoriesAction($type = 'service')
    {
        try {
            if($type == 'site')
                return Categories::findCategoriesForSite();

            $model = $this->categoryService->getModelByType($type);
            return $model::findAllCategories()->toArray();
        }catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_INVALID_CATEGORY_TYPE:
                    $exception = new Http404Exception(
                        _('URI not found or error in request.'), AbstractController::ERROR_NOT_FOUND,
                        new \Exception('URI not found: ' .
                            $this->request->getMethod() . ' ' . $this->request->getURI())
                    );
                    throw $exception;
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    //Now not uses moderator actions
    /*public function editCategoryAction()
    {
        if ($this->request->isPost()) {

            $category = Categories::findFirstByCategoryid($this->request->getPost('categoryId'));

            $category->setDescription($this->request->getPost('description'));
            $category->setImg($this->request->getPost('img'));
            $category->setParentId($this->request->getPost('parentId'));
            $category->setCategoryName($this->request->getPost('categoryName'));

            $category->save();
            return $category->save();

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function addCategoryAction()
    {
        if ($this->request->isPost()) {

            $category = new Categories();

            $category->setDescription($this->request->getPost('description'));
            $category->setImg($this->request->getPost('img'));
            $category->setParentId($this->request->getPost('parentId'));
            $category->setCategoryName($this->request->getPost('categoryName'));

            $category->save();
            return $category->save();

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function addSomeCategoriesAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();

            $categories = [['name' => 'Питание', 'child' => ['Рестораны', 'Бары, пабы', 'Столовые', 'Кофейни', 'Кондитерские, торты на заказ',
                'Быстрое питание', 'Доставка еды, воды', 'Кейтеринг', 'Другое'], 'img' => '/images/categories/питание.jpg'],
                ['name' => 'Развлечения и отдых', 'child' => [], 'img' => '/images/categories/развлечения-и-отдых.jpg'],
                ['name' => 'Авто и перевозки', 'child' => [], 'img' => '/images/categories/авто-и-перевозки.jpg'],
                ['name' => 'Красота', 'child' => [], 'img' => '/images/categories/красота.jpg'],
                ['name' => 'Спорт', 'child' => [], 'img' => '/images/categories/спорт.jpg'],
                ['name' => 'Медицина', 'child' => [], 'img' => '/images/categories/медицина.jpg'],
                ['name' => 'Недвижимость', 'child' => [], 'img' => '/images/categories/недвижимость.jpg'],
                ['name' => 'Ремонт и строительство', 'child' => [], 'img' => '/images/categories/ремонт-и-строительство.jpg'],
                ['name' => 'IT, интернет, телеком', 'child' => [], 'img' => '/images/categories/интернет-и-it.jpg'],
                ['name' => 'Деловые услуги', 'child' => [], 'img' => '/images/categories/деловые услуги.jpg'],
                ['name' => 'Курьерские поручения', 'child' => ['Курьерские услуги', 'Почтовые услуги', 'Доставка цветов',
                    'Другое'], 'img' => '/images/categories/курьерские-поручения.jpg'],
                ['name' => 'Бытовые услуги', 'child' => [], 'img' => '/images/categories/бытовые услуги.jpg'],
                ['name' => 'Клининг', 'child' => [], 'img' => '/images/categories/клининг.jpg'],
                ['name' => 'Обучение', 'child' => [], 'img' => '/images/categories/обучение.jpg'],
                ['name' => 'Праздники, мероприятия', 'child' => [], 'img' => '/images/categories/праздники.jpg'],
                ['name' => 'Животные', 'child' => [], 'img' => '/images/categories/животные.jpg'],
                ['name' => 'Реклама, полиграфия', 'child' => [], 'img' => '/images/categories/реклама.jpg'],
                ['name' => 'Сад, благоустройство', 'child' => [], 'img' => '/images/categories/сад.jpg'],
                ['name' => 'Охрана, безопасность', 'child' => [], 'img' => '/images/categories/охрана.jpg'],
                ['name' => 'Патронажн, уход', 'child' => [], 'img' => '/images/categories/уход.jpg'],
                ['name' => 'Друг на час', 'child' => [], 'img' => '/images/categories/друг-на-час.jpg'],
                ['name' => 'Благотворительность', 'child' => [], 'img' => '/images/categories/благотвортельность.jpg'],
                ['name' => 'Ритуальные услуги', 'child' => [], 'img' => '/images/categories/ритуальные-услуги.jpg'],
            ];

            $this->db->begin();
            foreach ($categories as $category) {
                $categoryObj = new Categories();
                $categoryObj->setCategoryName($category['name']);
                $categoryObj->setImg($category['img']);

                if (!$categoryObj->save()) {
                    $this->db->rollback();
                    $errors = [];
                    foreach ($categoryObj->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }

                foreach ($category['child'] as $child) {
                    $categoryObj2 = new Categories();
                    $categoryObj2->setCategoryName($child);
                    $categoryObj2->setParentId($categoryObj->getCategoryId());

                    if (!$categoryObj2->save()) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($categoryObj2->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                }
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }*/
}
