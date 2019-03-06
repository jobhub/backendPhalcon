<?php

namespace App\Controllers;

use App\Models\Tasks;
use App\Services\OfferService;
use App\Services\ProductService;
use App\Services\TagService;
use App\Services\TaskService;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

use App\Models\ImagesUsers;
use App\Models\News;
use App\Models\Accounts;

use App\Services\ImageService;
use App\Services\NewsService;
use App\Services\AccountService;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
/**
 * Class ProductController
 * Controller for work with products.
 * Implements CRUD for products. Tags, Images.
 */
class ProductController extends AbstractController
{
    /**
     * Добавляет товар
     *
     * @access private
     * @method POST
     *
     * @params product_name string
     * @params description string
     * @params category_id int
     * @params phone string | phone_id int
     * @params price int
     * @params account_id int
     * @params tags array of string
     * @params images in $_FILES
     *
     * @return string - json array  формате Status
     */
    public function addProductAction()
    {
        $inputData = json_decode(json_encode($this->request->getPost()));
        $data['product_name'] = $inputData->product_name;
        $data['description'] = $inputData->description;
        $data['category_id'] = $inputData->category_id;
        $data['phone_id'] = $inputData->phone_id;
        $data['phone'] = $inputData->phone;
        $data['price'] = $inputData->price;
        $data['account_id'] = $inputData->account_id;
        $data['tags'] = $inputData->tags;
        $data['images'] =$this->request->getUploadedFiles();

        try {
            $userId = self::getUserId();

            //проверки
            if (empty(trim($data['account_id']))) {
                $data['account_id'] = $this->accountService->getForUserDefaultAccount($userId)->getId();
            }

            if (!Accounts::checkUserHavePermission($userId, $data['account_id'], 'addProduct')) {
                throw new Http403Exception('Permission error');
            }

            $account = $this->accountService->getAccountById($data['account_id']);

            if($account->getCompanyId() == null){
                $errors['account_id'] = 'Only companies can add products';
            }

            if(!is_null($errors)){
                $errors['errors'] = true;
                $exception = new Http400Exception('Some parameters are invalid');
                throw $exception->addErrorDetails($errors);
            }

            $task = $this->productService->createProduct($data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ProductService::ERROR_UNABLE_CREATE_PRODUCT:
                case ImageService::ERROR_UNABLE_CHANGE_IMAGE:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                case TagService::ERROR_UNABLE_CREATE_TAG:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Product was successfully created', ['task' => $task->toArray()]);
    }

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
    public function getTasksForCurrentUserAction($company_id = null)
    {
        $userId = self::getUserId();

        if ($company_id != null) {
            if (!Accounts::checkUserHavePermissionToCompany($userId, $company_id, 'getTask')) {
                throw new Http403Exception('Permission error');
            }

            return Tasks::findTasksByCompany($company_id);
        }
        else
            return Tasks::findTasksByUser($userId);
    }

    /**
     * Return products with filters and query. Its for search.
     *
     * @method GET
     *
     * @param $id
     * @param $is_company
     *
     * @return string - массив заданий (Tasks)
     */
    public function getProductsAction($id, $is_company = false)
    {
        if ($is_company && strtolower($is_company)!="false")
            return Tasks::findAcceptingTasksByCompany($id);
        else
            return Tasks::findAcceptingTasksByUser($id);
    }

    /**
     * Deleting of the product
     *
     * @access private
     * @method DELETE
     *
     * @param $product_id
     *
     * @return string - json array в формате Status
     */
    public function deleteProductAction($product_id)
    {
        try {
            $userId = self::getUserId();

            $product = $this->productService->getProductById($product_id);

            if (!Accounts::checkUserHavePermission($userId, $product->getAccountId(), 'deleteProduct')) {
                throw new Http403Exception('Permission error');
            }

            $this->productService->deleteProduct($product);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ProductService::ERROR_UNABLE_DELETE_PRODUCT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ProductService::ERROR_PRODUCT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Product was successfully deleted');
    }

    /**
     * Editing of the product.
     *
     * @method PUT
     * @params product_id int
     * @params product_name string
     * @params description string
     * @params category_id int
     * @params phone string | phone_id int. If phone is boolean or string like boolean = false - set phone = null.
     * @params price int
     * @params account_id int
     * @params added_tags array of string
     * @params deleted_tags array of int
     * @return string - json array в формате Status
     */
    public function editProductAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['product_id'] = $inputData->product_id;
        $data['product_name'] = $inputData->product_name;
        $data['description'] = $inputData->description;
        $data['category_id'] = $inputData->category_id;
        $data['phone'] = $inputData->phone;
        $data['phone_id'] = $inputData->phone_id;
        $data['price'] = $inputData->price;
        $data['account_id'] = $inputData->account_id;
        $data['added_tags'] = $inputData->tags;
        $data['deleted_tags'] = $inputData->deleted_tags;

        try {
            //validation
            if (empty(trim($data['product_id']))) {
                $errors['product_id'] = 'Missing required parameter "product_id"';
            }

            if (!is_null($errors)) {
                $errors['errors'] = true;
                $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
                throw $exception->addErrorDetails($errors);
            }

            $userId = self::getUserId();

            $product = $this->productService->getProductById($data['product_id']);

            if (!Accounts::checkUserHavePermission($userId, $product->getAccountId(), 'editProduct')) {
                throw new Http403Exception('Permission error');
            }

            unset($data['product_id']);

            $this->productService->changeProduct($product, $data);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ProductService::ERROR_UNABLE_CHANGE_PRODUCT:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ProductService::ERROR_PRODUCT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Product was successfully changed');
    }
}