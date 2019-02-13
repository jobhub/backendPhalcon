<?php

namespace App\Services;

//libs
use App\Libs\SupportClass;
use App\Libs\ImageLoader;

//models
use App\Models\Accounts;
use App\Models\Companies;
use App\Models\ImagesModel;
use App\Models\ImagesNews;
use App\Models\ImagesRastreniya;
use App\Models\ImagesTemp;
use App\Models\ImagesUsers;
use App\Models\ImagesReviews;
use App\Models\ImagesServices;
use App\Models\Rastreniya;
use App\Models\Users;
use App\Models\News;
use App\Models\Services;
use App\Models\Reviews;
use App\Models\Binders;

//other
use Phalcon\DI\FactoryDefault as DI;
use Symfony\Component\EventDispatcher\Tests\Service;

/**
 * business logic for users
 *
 * Class UsersService
 */
class ImageService extends AbstractService
{

    const TYPE_USER = 'user';
    const TYPE_NEWS = 'news';
    const TYPE_REVIEW = 'review';
    const TYPE_SERVICE = 'service';
    const TYPE_COMPANY = 'company';
    const TYPE_TEMP = 'temp';
    const TYPE_RASTRENIYA = 'rastreniya';

    const ADDED_CODE_NUMBER = 7000;

    /** Unable to create user */
    const ERROR_INVALID_IMAGE_TYPE = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_IMAGE_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_IMAGE = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_IMAGE = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_SAVE_IMAGE = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_IMAGE = 6 + self::ADDED_CODE_NUMBER;

    const ERROR_HAVE_NOT_PERMISSION_TO_IMAGE_OBJECT = 7 + self::ADDED_CODE_NUMBER;

    const ERROR_UNABLE_CREATE_IMAGE_FROM_TEMP = 8 + self::ADDED_CODE_NUMBER;

    public function getImageById($id, $type)
    {
        try {
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
                case self::TYPE_TEMP:
                    $image = ImagesTemp::findImageById($id);
                    break;
                case self::TYPE_RASTRENIYA:
                    $image = ImagesRastreniya::findImageById($id);
                    break;
                default:
                    throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
            }
            if (!$image) {
                throw new ServiceException('Image not found', self::ERROR_IMAGE_NOT_FOUND);
            }
            return $image;
        }catch (\PDOException $e){
            throw new ServiceException($e->getMessage(), $e->getCode(),$e);
        }
    }

    public function getImages(int $objectId, $type, $page = 1, $page_size = ImagesModel::DEFAULT_RESULT_PER_PAGE)
    {
        switch ($type) {
            case self::TYPE_USER:
                //$images = ImagesUsers::findImagesForUser($objectId,$page,$page_size);
                $model = 'App\Models\ImagesUsers';
                break;
            case self::TYPE_NEWS:
                //$images = ImagesNews::findImagesForNews($objectId,$page,$page_size);
                $model = 'App\Models\ImagesNews';
                break;
            case self::TYPE_REVIEW:
                //$images = ImagesReviews::findImagesForReview($objectId,$page,$page_size);
                $model = 'App\Models\ImagesReviews';
                break;
            case self::TYPE_SERVICE:
                //$images = ImagesServices::findImagesForService($objectId,$page,$page_size);
                $model = 'App\Models\ImagesServices';
                break;
            case self::TYPE_TEMP:
                //$images = ImagesModel::findImages('App\Models\ImagesTemp',$objectId,$page,$page_size);
                $model = 'App\Models\ImagesTemp';
                break;
            case self::TYPE_RASTRENIYA:
                $model = 'App\Models\ImagesRastreniya';
                break;
            default:
                throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
        }
        $images = ImagesModel::findImages($model, $objectId, $page, $page_size);

        return $images;
    }

    public function getModelForType($type)
    {
        switch ($type) {
            case self::TYPE_USER:
                $model = 'App\Models\ImagesUsers';
                break;
            case self::TYPE_NEWS:
                $model = 'App\Models\ImagesNews';
                break;
            case self::TYPE_REVIEW:
                $model = 'App\Models\ImagesReviews';
                break;
            case self::TYPE_SERVICE:
                $model = 'App\Models\ImagesServices';
                break;
            case self::TYPE_TEMP:
                $model = 'App\Models\ImagesTemp';
                break;
            case self::TYPE_RASTRENIYA:
                $model = 'App\Models\ImagesRastreniya';
                break;
            default:
                $model = 'App\Models\ImagesModel';
        }
        return $model;
    }

    public function checkPermissionToObject($type, $objectId, $userId, $right)
    {
        switch ($type) {
            case self::TYPE_USER:
                /*$account = Accounts::findForUserDefaultAccount($objectId);

                if(!$account)
                    return false;

                $result = Accounts::checkUserHavePermission($userId,$account->getId(),$right);*/

                $object = Users::findFirstByUserId($userId);
                if ($objectId != null)
                    $result = $objectId == $userId;
                else
                    $result = true;
                    break;
            case self::TYPE_NEWS:
                $object = News::findFirstByNewsId($objectId);
                if (!$object)
                    return false;
                $result = Accounts::checkUserHavePermission($userId, $object->getAccountId(), $right);

                break;
            case self::TYPE_REVIEW:
                $object = Reviews::findFirstByReviewId($objectId);
                if (!$object)
                    return false;
                $result = Binders::checkUserHavePermission($userId, $object->getBinderId(),
                    $object->getBinderType(), $object->getExecutor(), $right);

                break;
            case self::TYPE_SERVICE:
                $object = Services::findFirstByServiceId($objectId);
                if (!$object)
                    return false;
                $result = Accounts::checkUserHavePermission($userId, $object->getAccountId(), $right);
                break;
            case self::TYPE_TEMP:
                /*$account = Accounts::findForUserDefaultAccount($objectId);

                if(!$account)
                    return false;

                $result = Accounts::checkUserHavePermission($userId,$account->getId(),$right);*/

                if($objectId!=null) {
                    $object = Accounts::findFirstById($objectId);
                } else{
                    $object = Accounts::findForUserDefaultAccount($userId);
                }
                if (!$object)
                    return false;
                $result = Accounts::checkUserHavePermission($userId, $object->getId(), $right);
                break;
            case self::TYPE_RASTRENIYA:
                $object = Rastreniya::findFirstById($objectId);
                if (!$object)
                    return false;

                $result = Accounts::checkUserHavePermission($userId, $object->getAccountId(), $right);
                break;
            default:
                throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
        }

        return $result ? $object : $result;
    }

    public function createImagesToUser($files, Users $user, $data = null)
    {
        return $this->createImagesToObject($files, $user, self::TYPE_USER, $data);
    }

    public function createImagesToNews($files, News $news)
    {
        return $this->createImagesToObject($files, $news, self::TYPE_NEWS);
    }

    public function createImagesToService($files, Services $service)
    {
        return $this->createImagesToObject($files, $service, self::TYPE_SERVICE);
    }

    public function createImagesToReview($files, Reviews $review)
    {
        return $this->createImagesToObject($files, $review, self::TYPE_REVIEW);
    }

    public function createImagesToObject($files, $some_object, $type, $data = null)
    {
        switch ($type) {
            case self::TYPE_USER:
                $path = 'users';
                $id = $some_object->getUserId();
                break;
            case self::TYPE_NEWS:
                $path = 'news';
                $id = $some_object->getNewsId();
                break;
            case self::TYPE_REVIEW:
                $path = 'reviews';
                $id = $some_object->getReviewId();
                break;
            case self::TYPE_SERVICE:
                $path = 'services';
                $id = $some_object->getServiceId();
                break;
            case self::TYPE_TEMP:
                $path = 'accounts/temp';
                $id = $some_object->getId();
                break;
            case self::TYPE_RASTRENIYA:
                $path = 'rastreniya';
                $id = $some_object->getId();
                break;
            default:
                throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
        }

        $imagesIds = [];
        $i = 0;
        foreach ($files as $file) {
            $newImage = $this->createImage($id, 'magic_string', $type, $data[$i]);

            $newImage = get_class($newImage)::findFirstByImageId($newImage->getImageId());

            if ($type == self::TYPE_NEWS && $file->getKey() == 'title')
                $imagesIds[] = ['image_id' => $newImage->getImageId(),'file_name' => $file->getKey()];
            else
                $imagesIds[] =['image_id' => $newImage->getImageId(),'file_name' => $newImage->getImageId()];

            $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

            if($type == self::TYPE_NEWS) {
                if ($imagesIds[$i]['file_name'] == 'title') {
                    $existsTitle = ImagesNews::findFirst(['object_id = :newsId: and image_path like :image_path:',
                        'bind' => ['newsId' => $some_object->getNewsId(),
                            'image_path' =>
                                '%'.ImageLoader::formFullImageName('news', '',
                                    $some_object->getNewsId(), $imagesIds[$i]['file_name']).'%',
                        ]]);

                    if ($existsTitle) {
                        $this->deleteImage($existsTitle);
                    }
                }
            }

            $filename = ImageLoader::formFullImageName($path, $imageFormat,
                $id, $imagesIds[count($imagesIds) - 1]['file_name']);

            /*if($type == self::TYPE_TEMP) {
                $further_filename = ImageLoader::formFullImageName('news', $imageFormat,
                    $id, 'temp_'.$imagesIds[count($imagesIds)-1]);

                $newImage->setFurtherPath($further_filename);
            }*/

            $this->changePathToImage($newImage, $filename);
            $i++;
        }
        return $imagesIds;
    }

    public function saveImagesToUser($files, Users $user, array $imagesIds)
    {
        return $this->saveImagesToObject($files, $user, $imagesIds, self::TYPE_USER);
    }

    public function saveImagesToNews($files, News $news, array $imagesIds)
    {
        return $this->saveImagesToObject($files, $news, $imagesIds, self::TYPE_NEWS);
    }

    public function saveImagesToService($files, Services $service, array $imagesIds)
    {
        return $this->saveImagesToObject($files, $service, $imagesIds, self::TYPE_SERVICE);
    }

    public function saveImagesToCompany($files, Companies $company, array $imagesIds)
    {
        return $this->saveImagesToObject($files, $company, $imagesIds, self::TYPE_COMPANY);
    }

    public function saveImagesToReview($files, Reviews $review, array $imagesIds)
    {
        return $this->saveImagesToObject($files, $review, $imagesIds, self::TYPE_REVIEW);
    }

    public function saveImagesToObject($files, $some_object, array $imagesIds, $type)
    {
        $i = 0;
        foreach ($files as $file) {
            switch ($type) {
                case self::TYPE_USER:
                    $result = ImageLoader::loadUserPhoto($file->getTempName(), $file->getName(),
                        $some_object->getUserId(), $imagesIds[$i]['file_name']);
                    break;
                case self::TYPE_NEWS:
                    $result = ImageLoader::loadNewsImage($file->getTempName(), $file->getName(),
                        $some_object->getNewsId(), $imagesIds[$i]['file_name']);
                    break;
                case self::TYPE_REVIEW:
                    $result = ImageLoader::loadReviewImage($file->getTempName(), $file->getName(),
                        $some_object->getReviewId(), $imagesIds[$i]['file_name']);
                    break;
                case self::TYPE_SERVICE:
                    $result = ImageLoader::loadServiceImage($file->getTempName(), $file->getName(),
                        $some_object->getServiceId(), $imagesIds[$i]['file_name']);
                    break;
                case self::TYPE_COMPANY:
                    $result = ImageLoader::loadCompanyLogotype($file->getTempName(), $file->getName(),
                        $some_object->getCompanyId(), $imagesIds[$i]['file_name']);
                    break;
                case self::TYPE_TEMP:
                    $result = ImageLoader::loadTempImage($file->getTempName(), $file->getName(),
                        $some_object->getId(), $imagesIds[$i]['file_name']);
                    break;
                case self::TYPE_RASTRENIYA:
                    $result = ImageLoader::loadRastImage($file->getTempName(), $file->getName(),
                        $some_object->getId(), $imagesIds[$i]['file_name']);
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
                    self::ERROR_UNABLE_SAVE_IMAGE, null, null, [$error]);
            }
        }

        return true;
    }

    private function createImage(int $id, string $pathToImage, $type, $data = null)
    {
        switch ($type) {
            case self::TYPE_USER:
                $image = new ImagesUsers();
                $image->setImageText($data/*['image_text']*/);
                break;
            case self::TYPE_NEWS:
                $image = new ImagesNews();
                break;
            case self::TYPE_REVIEW:
                $image = new ImagesReviews();
                break;
            case self::TYPE_SERVICE:
                $image = new ImagesServices();
                break;
            case self::TYPE_TEMP:
                $image = new ImagesTemp();
                $image->setFurtherPath($data['further_path'] == null ? 'magic' : $data['further_path']);
                break;
            case self::TYPE_RASTRENIYA:
                $image = new ImagesRastreniya();
                break;
            default:
                throw new ServiceException('Invalid type of image', self::ERROR_INVALID_IMAGE_TYPE);
        }
        $image->setObjectId($id);
        $image->setImagePath($pathToImage);

        if (!$image->create()) {
            $errors = SupportClass::getArrayWithErrors($image);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_CREATE_IMAGE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_CREATE_IMAGE);
            }
        }

        return $image;
    }

    public function changePathToImage(ImagesModel $image, string $pathToImage)
    {
        $image->setImagePath($pathToImage);

        if (!$image->update()) {
            $errors = SupportClass::getArrayWithErrors($image);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_CHANGE_IMAGE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable save image',
                    self::ERROR_UNABLE_CHANGE_IMAGE);
            }
        }

        return $image;
    }

    public function deleteImage(ImagesModel $image)
    {
        if (!$image->delete()) {
            $errors = SupportClass::getArrayWithErrors($image);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable delete image',
                    self::ERROR_UNABLE_DELETE_IMAGE, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable delete image',
                    self::ERROR_UNABLE_DELETE_IMAGE);
            }
        }

        return $image;
    }

    public function setCompanyLogotype(Companies $company, $file)
    {
        $this->saveImagesToCompany([$file], $company, [$company->getCompanyId()]);

        $format = pathinfo($file->getName(), PATHINFO_EXTENSION);

        $logotype = ImageLoader::formFullImageName('companies', $format,
            $company->getCompanyId(), $company->getCompanyId());

        $di = DI::getDefault();

        $di->getCompanyService()->changeCompany($company, ['logotype' => $logotype]);

        return $logotype;
    }

    public function transferTempImageToNewsObject(ImagesTemp $tempImage, $news_id)
    {

        //Создание объекта для изображения в базе
        $newImage = $this->createImage($news_id, 'magic_string', self::TYPE_NEWS);

        $newImage = get_class($newImage)::findFirstByImageId($newImage->getImageId());

        $imageId = $newImage->getImageId();

        $imageFormat = pathinfo($tempImage->getImagePath(), PATHINFO_EXTENSION);

        $filename = ImageLoader::formFullImageName('news', $imageFormat,
            $news_id, $imageId);

        $this->changePathToImage($newImage, $filename);

        return $filename;
    }

    public function transferTempImageToNewsFile(ImagesTemp $tempImage, $news_id, $filename)
    {
        //объект в базе создан. Осталось переместить
        if(!is_dir(IMAGE_PATH . 'news' . '/'. $news_id)) {
            $result = mkdir(IMAGE_PATH . 'news' . '/'. $news_id);

            if(!$result)
                throw new ServiceException('Не удалось переместить временное изображение в новости',
                    self::ERROR_UNABLE_CREATE_IMAGE_FROM_TEMP);
        }

        $result = copy(BASE_PATH.'/public/'.$tempImage->getImagePath(),
            BASE_PATH.'/public/'.$filename);

        if (!$result)
            throw new ServiceException('Не удалось переместить временное изображение в новости',
                self::ERROR_UNABLE_CREATE_IMAGE_FROM_TEMP);

        $this->deleteImage($tempImage);
        return $filename;
    }
}
