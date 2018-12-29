<?php

namespace App\Controllers;

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
class CategoriesAPIController extends \App\Controllers\AbstractController
{
    /**
     * Возвращает категории
     *
     * @method GET
     *
     */
    public function getCategoriesAction()
    {
        return Categories::findAllCategories()->toArray();
    }

    /**
     * Возвращает категории в удобном для сайта виде
     *
     * @method GET
     *
     */
    public function getCategoriesForSiteAction()
    {
        return Categories::findCategoriesForSite();
    }

    /**
     * Подписывает текущего пользователя на указанную категорию.
     * @access private
     * @method POST
     * @params category_id, radius
     * @return string - json array Status
     */
    public function setFavouriteAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        try{
            $this->categoryService->setFavourite($userId,$data['category_id'],$data['radius']);
        }catch(ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_UNABlE_SUBSCRIBE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_ALREADY_SIGNED:
                    throw new Http500Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return self::successResponse('User was successfully subscribed');
    }

    /**
     * Меняет радиус на получение уведомлений для подписки на категорию
     * @method PUT
     * @params radius, categoryId
     * @return string - json array Status
     */
    public function editRadiusInFavouriteAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        try{
            $this->categoryService->editRadius($userId,$data['category_id'],$data['radius']);
        }catch(ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_UNABlE_CHANGE_RADIUS:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_DON_NOT_SIGNED:
                    throw new Http500Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Radius successfully changed');
    }

    /**
     * Отписывает текущего пользователя от категории
     * @method DELETE
     * @param $category_id
     * @return string - json array Status
     */
    public function deleteFavouriteAction($category_id)
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        try{
            $this->categoryService->deleteFavourite($userId,$category_id);
        }catch(ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case CategoryService::ERROR_UNABlE_UNSUBSCRIBE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('User was successfully unsubscribed');
    }

    /**
     * Возвращает все подписки пользователя на категории
     * @GET
     * @return string - json array - подписки пользователя
     */
    public function getFavouritesAction()
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        return FavoriteCategories::findForUser($userId)->toArray();
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
