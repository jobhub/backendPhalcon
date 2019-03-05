<?php

namespace App\Services;

use App\Models\Products;
use Phalcon\DI\FactoryDefault as DI;

use App\Models\Offers;
use App\Models\Tasks;

use App\Libs\SupportClass;

use Phalcon\Http\Request\File as PhalconFile;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ProductService extends AbstractService
{
    const ADDED_CODE_NUMBER = 36000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_PRODUCT = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_PRODUCT_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_PRODUCT = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_PRODUCT = 4 + self::ADDED_CODE_NUMBER;

    /**
     * Created product. Added tags. Added images.
     *
     * @param array $productData - [product_name, description,
     *                              phone | phone_id,
     *                              category_id, date_creation, price, account_id,
     *                              tags =>[strings],
     *                              images => [Phalcon\Http\Request\File]
     *                              ]
     *
     * @return Products
     */
    public function createProduct(array $productData)
    {
        try {
            $this->db->begin();
            $product = new Products();

            $this->fillProduct($product, $productData);

            if ($product->create() == false) {
                $this->db->rollback();
                SupportClass::getErrorsWithException($product, self::ERROR_UNABLE_CREATE_PRODUCT, 'Unable to create product');
            }

            if (isset($productData['tags']) && is_array($productData['tags'])) {
                foreach ($productData['tags'] as $tag) {
                    if (is_string($tag))
                        $this->tagService->addTagToObject($tag, $product->getProductId(), TagService::TYPE_PRODUCT);
                }
            }

            if (isset($productData['images']) && is_array($productData['images'])) {
                $ids = $this->imageService->createImagesToObject($productData['images'], $product, ImageService::TYPE_PRODUCT);

                $this->imageService->saveImagesToObject($productData['images'], $product, $ids, ImageService::TYPE_PRODUCT);
            }
        }catch (\PDOException $e){
            $this->db->rollback();
            throw new ServiceException($e->getMessage(),$e->getCode(),$e);
        }

        $this->db->commit();
        return $product;
    }

    public function getProductById(int $productId)
    {
        $product = Products::findProductById($productId);

        if (!$product) {
            throw new ServiceException('Product does not exist', self::ERROR_PRODUCT_NOT_FOUND);
        }
        return $product;
    }

    public function fillProduct(Products $product, array $data)
    {
        if (!empty(trim($data['product_name'])))
            $product->setProductName($data['product_name']);

        if (!empty(trim($data['category_id'])))
            $product->setCategoryId($data['category_id']);

        if (isset($data['phone'])){
            $phoneDeleted = filter_var($data['phone'],FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);

            if(!is_null($phoneDeleted) && $phoneDeleted){
                $product->setPhoneId(null);
            } else {
                $phoneObject = $this->phoneService->createPhone($data['phone']);
                $product->setPhoneId($phoneObject->getPhoneId());
            }

        }elseif (!empty(trim($data['phone_id']))){
            $product->setPhoneId($data['phone_id']);
        }

        if (isset($data['description']))
            $product->setDescription($data['description']);

        if (!empty(trim($data['date_creation'])))
            $product->setDateCreation(date('Y-m-d H:i:s', strtotime($data['date_creation'])));

        if (isset($data['price']))
            $product->setPrice($data['price']);

        if (!empty(trim($data['account_id'])))
            $product->setAccountId($data['account_id']);
    }

    public function deleteProduct(Products $product)
    {
        if ($product->delete() == false) {
            SupportClass::getErrorsWithException($product, self::ERROR_UNABLE_DELETE_PRODUCT, 'Unable to delete product');
        }

        return $product;
    }

    /**
     * Changing of the product. Added and deleted tags, if it necessary.
     *
     * @param Products $product
     * @param array $productData - [product_name, description,
     *                              phone | phone_id
     *                              category_id, date_creation, price, account_id,
     *                              added_tags =>[strings], deleted_tags =>[int]
     *                              ]
     * @return Products
     */
    public function changeProduct(Products $product, array $productData)
    {
        $this->fillProduct($product, $productData);

        if ($product->update() == false) {
            SupportClass::getErrorsWithException($product, self::ERROR_UNABLE_CHANGE_PRODUCT, 'Unable to change task');
        }

        if (isset($productData['deleted_tags']) && is_array($productData['deleted_tags'])) {
            foreach ($productData['deleted_tags'] as $tagId) {
                $productTag = $this->tagService->getTagForObject($tagId, $product->getProductId(), TagService::TYPE_PRODUCT);
                $this->tagService->deleteTagFromObject($productTag);
            }
        }

        if (isset($productData['added_tags']) && is_array($productData['added_tags'])) {
            foreach ($productData['added_tags'] as $tag) {
                $this->tagService->addTagToObject($tag, $product->getProductId(), TagService::TYPE_PRODUCT);
            }
        }

        return $product;
    }
}
