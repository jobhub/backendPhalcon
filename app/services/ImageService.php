<?php

namespace App\Services;

//libs
use App\Libs\SupportClass;
use App\Libs\ImageLoader;

//models
use App\Models\Companies;
use App\Models\ImagesModel;
use App\Models\ImagesNews;
use App\Models\ImagesUsers;
use App\Models\ImagesReviews;
use App\Models\ImagesServices;
use App\Models\Users;
use App\Models\News;
use App\Models\Services;

//other
use Phalcon\DI\FactoryDefault as DI;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ImageService extends AbstractService
{

    const TYPE_USER = 0;
    const TYPE_NEWS = 1;
    const TYPE_REVIEW = 2;
    const TYPE_SERVICE = 3;
    const TYPE_COMPANY = 4;

    const ADDED_CODE_NUMBER = 7000;

    /** Unable to create user */
    const ERROR_INVALID_IMAGE_TYPE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_IMAGE_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_IMAGE = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_IMAGE = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_SAVE_IMAGE = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_IMAGE = 6 + self::ADDED_CODE_NUMBER;

    public function getImageById(int $id, int $type)
    {
        switch ($type) {
            case self::TYPE_USER:
                $image = ImagesUsers::findImageById($id);
                break;
            case self::TYPE_NEWS:
                $image = ImagesNews::findImageById($id);
                break;
            case self::TYPE_REVIEW:
                $image = ImagesReviews::findImageById($id);
                break;
            case self::TYPE_SERVICE:
                $image = ImagesServices::findImageById($id);
                break;
            default:
                throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
        }
        if (!$image) {
            throw new ServiceException('Image not found', self::ERROR_IMAGE_NOT_FOUND);
        }
        return $image;
    }

    /*public function addImagesToUser($files, Users $user)
    {
        if (count($files) <= 0) {
            return true;
        }
        $imagesIds = [];

        //$di->db->begin();

        try {
            foreach ($files as $file) {
                $newImage =$this->createImage($user->getUserId(),'magic_string',self::TYPE_USER);

                $imagesIds[] = $newImage->getImageId();

                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $filename = ImageLoader::formFullImageName('users', $imageFormat,
                    $user->getUserId(), $newImage->getImageId());

                $this->changePathToImage($newImage,$filename);
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadUserPhoto($file->getTempName(), $file->getName(),
                    $user->getUserId(), $imagesIds[$i]);
                $i++;
                if ($result != ImageLoader::RESULT_ALL_OK || $result === null) {
                    if ($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED) {
                        $error = 'Формат одного из изображений не поддерживается';
                    } elseif ($result == ImageLoader::RESULT_ERROR_NOT_SAVED) {
                        $error = 'Не удалось сохранить изображение';
                    } else {
                        $error = 'Ошибка при загрузке изображения';
                    }

                    throw new ServiceExtendedException('Unable save image',
                        self::ERROR_UNABLE_SAVE_IMAGE,null,null,[$error]);
                }
            }
        } catch(\Exception $e) {
            //$di->getDb()->rollback();
            throw $e;
        }

        $di->getDb()->commit();
        return true;
    }*/

    public function createImagesToUser($files, Users $user){
        return $this->createImagesToObject($files,$user,self::TYPE_USER);
    }

    public function createImagesToNews($files, News $news){
        return $this->createImagesToObject($files,$news,self::TYPE_NEWS);
    }

    public function createImagesToService($files, Services $service){
        return $this->createImagesToObject($files,$service,self::TYPE_SERVICE);
    }

    private function createImagesToObject($files, $some_object, $type){

        switch ($type) {
            case self::TYPE_USER:
                $id = $some_object->getUserId();
                $path = 'users';
                break;
            case self::TYPE_NEWS:
                $id = $some_object->getNewsId();
                $path = 'news';
                break;
            case self::TYPE_REVIEW:
                $id = $some_object->getReviewId();
                $path = 'reviews';
                break;
            case self::TYPE_SERVICE:
                $id = $some_object->getServiceId();
                $path = 'services';
                break;
            default:
                throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
        }

        $imagesIds = [];
        foreach ($files as $file) {
            $newImage = $this->createImage($id,'magic_string',$type);

            if($type == self::TYPE_NEWS && $file->getKey() == 'title')
                $imagesIds[] =  $file->getKey();
            else
                $imagesIds[] = $newImage->getImageId();

            $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

            $filename = ImageLoader::formFullImageName($path, $imageFormat,
                $id, $imagesIds[count($imagesIds)-1]);

            $this->changePathToImage($newImage,$filename);
        }
        return $imagesIds;
    }

    public function saveImagesToUser($files, Users $user,array $imagesIds){
        return $this->saveImagesToObject($files,$user,$imagesIds,self::TYPE_USER);
    }

    public function saveImagesToNews($files, News $news,array $imagesIds){
        return $this->saveImagesToObject($files,$news,$imagesIds,self::TYPE_NEWS);
    }

    public function saveImagesToService($files, Services $service,array $imagesIds){
        return $this->saveImagesToObject($files,$service,$imagesIds,self::TYPE_SERVICE);
    }

    public function saveImagesToCompany($files, Companies $company,array $imagesIds){
        return $this->saveImagesToObject($files,$company,$imagesIds,self::TYPE_COMPANY);
    }

    private function saveImagesToObject($files, $some_object, array $imagesIds, int $type){
        $i = 0;
        foreach ($files as $file) {
            switch ($type) {
                case self::TYPE_USER:
                    $result = ImageLoader::loadUserPhoto($file->getTempName(), $file->getName(),
                        $some_object->getUserId(), $imagesIds[$i]);
                    break;
                case self::TYPE_NEWS:
                    $result = ImageLoader::loadNewsImage($file->getTempName(), $file->getName(),
                        $some_object->getNewsId(), $imagesIds[$i]);
                    break;
                case self::TYPE_REVIEW:
                    $result = ImageLoader::loadReviewImage($file->getTempName(), $file->getName(),
                        $some_object->getReviewId(), $imagesIds[$i]);
                    break;
                case self::TYPE_SERVICE:
                    $result = ImageLoader::loadServiceImage($file->getTempName(), $file->getName(),
                        $some_object->getServiceId(), $imagesIds[$i]);
                    break;
                case self::TYPE_COMPANY:
                    $result = ImageLoader::loadCompanyLogotype($file->getTempName(), $file->getName(),
                        $some_object->getCompanyId(), $imagesIds[$i]);
                    break;
                default:
                    throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
            }
            $i++;
            if ($result != ImageLoader::RESULT_ALL_OK || $result === null) {
                if ($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED) {
                    $error = 'Формат одного из изображений не поддерживается';
                } elseif ($result == ImageLoader::RESULT_ERROR_NOT_SAVED) {
                    $error = 'Не удалось сохранить изображение';
                } else {
                    $error = 'Ошибка при загрузке изображения';
                }

                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_SAVE_IMAGE,null,null,[$error]);
            }
        }

        return true;
    }

    private function createImage(int $id, string $pathToImage,int $type){
        switch ($type) {
            case self::TYPE_USER:
                $image = new ImagesUsers();
                $image->setUserId($id);
                break;
            case self::TYPE_NEWS:
                $image = new ImagesNews();
                $image->setNewsId($id);
                break;
            case self::TYPE_REVIEW:
                $image = new ImagesReviews();
                $image->setReviewId($id);
                break;
            case self::TYPE_SERVICE:
                $image = new ImagesServices();
                $image->setServiceId($id);
                break;
            default:
                throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
        }

        $image->setImagePath($pathToImage);

        if(!$image->save()){
            $errors = SupportClass::getArrayWithErrors($image);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_CREATE_IMAGE,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_CREATE_IMAGE);
            }
        }

        return $image;
    }

    public function changePathToImage(ImagesModel $image, string $pathToImage){
        $image->setImagePath($pathToImage);

        if(!$image->update()){
            $errors = SupportClass::getArrayWithErrors($image);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_CHANGE_IMAGE,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_CHANGE_IMAGE);
            }
        }

        return $image;
    }

    public function deleteImage(ImagesModel $image){
        if(!$image->delete()){
            $errors = SupportClass::getArrayWithErrors($image);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable delete image',
                    self::ERROR_UNABLE_DELETE_IMAGE,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable delete image',
                    self::ERROR_UNABLE_DELETE_IMAGE);
            }
        }

        return $image;
    }

    public function setCompanyLogotype(Companies $company, $file){
        $this->saveImagesToCompany([$file],$company,[$company->getCompanyId()]);

        $format = pathinfo($file->getName(),PATHINFO_EXTENSION);

        $logotype = ImageLoader::formFullImageName('companies',$format,
            $company->getCompanyId(),$company->getCompanyId());

        $di = DI::getDefault();

        $di->companyService->changeCompany($company,['logotype'=>$logotype]);

        return $logotype;
    }
}
