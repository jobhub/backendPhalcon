<?php

namespace App\Controllers;

use App\Models\Products;
use App\Models\Tasks;
use App\Services\CompanyService;
use App\Services\OfferService;
use App\Services\PhoneService;
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

        $this->db->begin();
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

            $product = $this->productService->createProduct($data);
            $product = $this->productService->getProductById($product->getProductId());

            $handledProduct = Products::handleProductFromArray($product->toArray());
            $this->db->commit();
            return self::successResponse('Product was successfully created',
                ['product' => $handledProduct]);
        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ProductService::ERROR_UNABLE_CREATE_PRODUCT:
                case ImageService::ERROR_UNABLE_CHANGE_IMAGE:
                case ImageService::ERROR_UNABLE_CREATE_IMAGE:
                case ImageService::ERROR_UNABLE_SAVE_IMAGE:
                case TagService::ERROR_UNABLE_CREATE_TAG:
                case PhoneService::ERROR_UNABLE_CREATE_PHONE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case ImageService::ERROR_INVALID_IMAGE_TYPE:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
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
     * @access private
     * @method PUT
     * @params product_id int
     * @params product_name string
     * @params description string
     * @params category_id int
     * @params phone string | phone_id int. If phone is boolean or string like boolean = false - set phone = null.
     * @params price int
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

    /**
     * Возвращает публичную информацию о товаре.
     * @access public.
     *
     * @method GET
     *
     * @param product_id
     * @params $account_id
     *
     * @return array
     */
    public function getProductInfoAction($product_id)
    {
        try {
            $inputData = $this->request->getQuery();
            $account_id = $inputData['account_id'];
            $product = $this->productService->getProductById($product_id);

            if(self::isAuthorized()) {
                $userId = self::getUserId();

                $account = $this->accountService->checkPermissionOrGetDefaultAccount($userId, $account_id);

                self::setAccountId($account->getId());
            }

            return self::successResponse('',Products::handleProductFromArray($product->toArray()));

        }catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ProductService::ERROR_PRODUCT_NOT_FOUND:
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Возвращает товары в соответствии с фильтрами
     * @access private.
     *
     * @method POST
     *
     * @params account_id int
     * @params sort string : "price asc", "price desc", "date desc"
     * @params query string
     * @params page int
     * @params page_size int
     *
     * @params category_id int
     * @params city_id int
     * @params distance int  - must be > 1 and < 200 or null.
     * @params company_id int
     * @params center [latitude, longitude]
     * @params price_min int
     * @params price_max int
     *
     */
    public function findProductsAction()
    {
        try {
            $inputData = json_decode($this->request->getRawBody(),true);
            $data['account_id'] = $inputData['account_id'];

            $data['query'] = $inputData['query'];

            $data['category_id'] = $inputData['category_id'];
            $data['city_id'] = $inputData['city_id'];

            $data['company_id'] = $inputData['company_id'];

            $data['page'] = $inputData['page'];
            $data['page_size'] = $inputData['page_size'];

            $data['center'] = $inputData['center'];
            $data['distance'] = $inputData['distance'];

            $data['price_max'] = $inputData['price_max'];
            $data['price_min'] = $inputData['price_min'];

            $data['sort'] = $inputData['sort'];

            //validation
            $data['category_id'] = filter_var($data['category_id'],FILTER_VALIDATE_INT);

            $data['page'] = filter_var($data['page'],FILTER_VALIDATE_INT);
            $data['page'] = (!$data['page'])?1:$data['page'];

            $data['page_size'] = filter_var($data['page_size'],FILTER_VALIDATE_INT);
            $data['page_size'] = (!$data['page_size'])?Products::DEFAULT_RESULT_PER_PAGE:$data['page_size'];

            $data['price_max'] = filter_var($data['price_max'],FILTER_VALIDATE_INT);
            $data['price_max'] = ($data['price_max']<0 || !$data['price_max'])? null : $data['price_max'];

            $data['price_min'] = filter_var($data['price_min'],FILTER_VALIDATE_INT);
            $data['price_min'] = ($data['price_min']<0|| !$data['price_min'])? null : $data['price_min'];

            $data['distance'] = filter_var($data['distance'],FILTER_VALIDATE_INT);
            $data['distance'] = ($data['distance'] < 1 || !$data['distance'] || $data['distance'] > 200)? null : $data['distance'];

            if(!empty($data['center']['longitude']) && !empty($data['center']['latitude'])){
                $data['center']['longitude'] = filter_var($data['center']['longitude'],FILTER_VALIDATE_FLOAT);
                $data['center']['latitude'] = filter_var($data['center']['latitude'],FILTER_VALIDATE_FLOAT);

                if(empty($data['center']['longitude']) || empty($data['center']['latitude']))
                    $data['center'] = null;
            } else{
                $data['center'] = null;
            }

            $data['company_id'] = filter_var($data['company_id'],FILTER_VALIDATE_INT);

            if(!empty($data['company_id'])) {
                $company = $this->companyService->getCompanyById($data['company_id']);

                $errors = null;
                if (!$company->getIsShop()) {
                    $errors['company_id'] = 'Filtered company must be shop';
                }
            }

            $data['city_id'] = filter_var($data['city_id'],FILTER_VALIDATE_INT);


            if(!is_null($errors)){
                $errors['error'] = true;
                $exception = new Http400Exception("Invalid some parameters");
                throw $exception->addErrorDetails($errors);
            }

            if(self::isAuthorized()) {
                $userId = self::getUserId();

                $account = $this->accountService->checkPermissionOrGetDefaultAccount($userId, $data['account_id']);

                self::setAccountId($account->getId());
            }
            $filter =[];
            if(!empty($data['category_id']))
                $filter['categories'] = [$data['category_id']];

            if(!empty($data['city_id']))
                $filter['cities'] = [$data['city_id']];

            if(!empty($data['company_id']))
                $filter['companies'] = [$data['company_id']];

            if(!empty($data['price_max']))
                $filter['price_max'] = $data['price_max'];

            if(!empty($data['price_min']))
                $filter['price_min'] = $data['price_min'];

            if(!empty($data['distance']))
                $filter['distance'] = $data['distance'];

            if(!empty($data['center']))
                $filter['center'] = $data['center'];

            /*$filter = [
                'categories'=>[$data['category_id']],
                'cities'    =>[$data['city']],
                'companies' =>[$data['company']],
                'price_max' =>$data['price_max'],
                'price_min' =>$data['price_min'],
                'distance' =>$data['distance'],
                'center' =>$data['center'],
            ];*/

            $products = Products::findProductsWithFilters($data['query'],$filter,$data['sort'],
                $data['page'],$data['page_size']);

            return self::successPaginationResponse('',$products['data'],$products['pagination']);

        }catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ProductService::ERROR_PRODUCT_NOT_FOUND:
                case AccountService::ERROR_ACCOUNT_NOT_FOUND:
                case CompanyService::ERROR_COMPANY_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }


}