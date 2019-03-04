<?php

namespace App\Services;

use App\Libs\ImageLoader;
use App\Models\Accounts;
use App\Models\FavoriteUsers;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

//models
use App\Models\Userinfo;
use App\Models\FavoriteCompanies;
use App\Models\Settings;
use Phalcon\Http\Client\Exception;

use Phalcon\Http\Request\File as PhalconFile;

/**
 * business logic for users
 *
 * Class UsersService
 */
class UserInfoService extends AbstractService
{

    const ADDED_CODE_NUMBER = 4000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_USER_INFO = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_SETTINGS = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_USER_INFO_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_USER_INFO = 4 + self::ADDED_CODE_NUMBER;

    public function createUserInfo(array $userInfoData)
    {
        try {
            $userInfo = new Userinfo();
            $userInfo->setUserId($userInfoData['user_id']);

            $this->fillUserInfo($userInfo, $userInfoData);

            if ($userInfo->save() == false) {
                $errors = SupportClass::getArrayWithErrors($userInfo);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable create info for user',
                        self::ERROR_UNABLE_CREATE_USER_INFO, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable create info for user',
                        self::ERROR_UNABLE_CREATE_USER_INFO);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $userInfo;
    }

    public function changeUserInfo(Userinfo $userInfo, array $userInfoData)
    {
        try {
            $this->fillUserInfo($userInfo, $userInfoData);
            if ($userInfo->update() == false) {
                $errors = SupportClass::getArrayWithErrors($userInfo);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable change info for user',
                        self::ERROR_UNABLE_CHANGE_USER_INFO, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable change info for user',
                        self::ERROR_UNABLE_CHANGE_USER_INFO);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $userInfo;
    }

    private function fillUserInfo(Userinfo $userInfo, array $data)
    {
        if (!empty(trim($data['first_name'])))
            $userInfo->setFirstName($data['first_name']);
        if (!empty(trim($data['last_name'])))
            $userInfo->setLastName($data['last_name']);
        if (isset($data['patronymic']))
            $userInfo->setPatronymic($data['patronymic']);

        if (isset($data['male']))
            $userInfo->setMale($data['male']);

        if (isset($data['city_id']) && SupportClass::checkInteger($data['city_id'])) {
            $city = $this->cityService->getCityById($data['city_id']);
            $userInfo->setCityId($data['city_id']);
        }
        if (!empty(trim($data['birthday'])))
            $userInfo->setBirthday(date('Y-m-d H:i:sO', strtotime($data['birthday'])));
        if (isset($data['about']))
            $userInfo->setAbout($data['about']);
        if (isset($data['status']))
            $userInfo->setStatus($data['status']);
        if (isset($data['email']))
            $userInfo->setEmail($data['email']);
        if (isset($data['path_to_photo']))
            $userInfo->setPathToPhoto($data['path_to_photo']);
        if (isset($data['nickname']))
            $userInfo->setNickname($data['nickname']);
        if (isset($data['website']))
            $userInfo->setWebsite($data['website']);

        if(isset($data['delete_photo']) && $data['delete_photo'])
            $userInfo->setPathToPhoto(null);

    }

    public function CreateSettings(int $userId)
    {
        try {
            $setting = new Settings();
            $setting->setUserId($userId);

            if ($setting->create() == false) {
                $errors = SupportClass::getArrayWithErrors($setting);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable create settings',
                        self::ERROR_UNABLE_CREATE_SETTINGS, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable create settings',
                        self::ERROR_UNABLE_CREATE_SETTINGS);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $setting;
    }

    public function getUserInfoById(int $userId)
    {
        try {
            $user = Userinfo::findFirstByUserId($userId);

            if (!$user || $user == null) {
                throw new ServiceException('User don\'t exists', self::ERROR_USER_INFO_NOT_FOUND);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $user;
    }

    public function getHandledUserInfoById(int $userId, Accounts $accountReceiver = null)
    {
        try {
            $userInfo = Userinfo::findUserInfoById($userId);

            if (!$userInfo || $userInfo == null) {
                throw new ServiceException('User don\'t exists', self::ERROR_USER_INFO_NOT_FOUND);
            }

            return Userinfo::handleUserInfo($userInfo, $accountReceiver);
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $nickname
     * @return bool - true if exists
     */
    public function checkNicknameExists(string $nickname)
    {
        try {
            $user = Userinfo::findFirstByNickname($nickname);
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $user?true:false;
    }


    public function savePhotoForUserByURL(Users $user, Userinfo $userInfo, $url, $photo_name){
        SupportClass::writeMessageInLogFile('Полученный URL '.$url .', название - '.$photo_name);

        $imagePath = ImageLoader::formFullImagePathFromImageName('temp',$user->getUserId(),$photo_name);

        $imagePath = BASE_PATH.'/public/'.$imagePath;

        $imagePath = str_replace('\\','/',$imagePath);

        SupportClass::writeMessageInLogFile('Сформированное новое название '.$imagePath);

        if(!is_dir(IMAGE_PATH . '/temp/' . $user->getUserId())) {
            $result = mkdir(IMAGE_PATH . '/temp/' . $user->getUserId());
            if(!$result)
                throw new ServiceException('Unable create directory for temp image',ImageService::ERROR_UNABLE_CREATE_IMAGE);
        }

        $file = SupportClass::downloadFile($url,$imagePath);

        $strFile = var_export($file,true);

        SupportClass::writeMessageInLogFile('Файл '.$strFile);

        if(empty($file)){
            throw new ServiceExtendedException('Не удалось загрузить файл по полученной из соц. сети ссылке',
                ImageService::ERROR_UNABLE_SAVE_IMAGE);
        }

        $phalcon_file = new PhalconFile([
            'name'=>$photo_name,
            'tmp_name'=>$imagePath,
            'size'=>filesize($imagePath)
        ]);

        $ids = $this->imageService->createImagesToUser(array($phalcon_file), $user);

        $strIds = var_export($ids,true);

        SupportClass::writeMessageInLogFile('Созданные Id изображений '.$strIds);

        $this->imageService->saveImagesToUser(array($phalcon_file), $user, $ids);

        $image = $this->imageService->getImageById($ids[0]['image_id'],ImageService::TYPE_USER);

        $this->changeUserInfo($userInfo,['path_to_photo' => $image->getImagePath()]);

        unlink($imagePath);
    }
    /*public function subscribeToCompany(int $userId, int $companyId)
    {
        try {
            $fav = new FavoriteCompanies();
            $fav->setSubjectId($userId);
            $fav->setObjectId($companyId);

            if (!$fav->create()) {
                $errors = SupportClass::getArrayWithErrors($fav);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable subscribe user to company',
                        self::ERROR_UNABLE_SUBSCRIBE_USER_TO_COMPANY, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable subscribe user to company',
                        self::ERROR_UNABLE_SUBSCRIBE_USER_TO_COMPANY);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getSigningToCompany(int $userId, int $companyId)
    {
        $fav = FavoriteCompanies::findByIds('App\Models\FavoriteCompanies', $userId, $companyId);

        if (!$fav) {
            throw new ServiceException('User don\'t subscribe to company', self::ERROR_USER_NOT_SUBSCRIBED_TO_COMPANY);
        }
        return $fav;
    }

    public function unsubscribeFromCompany(FavoriteCompanies $favComp)
    {
        if (!$favComp->delete()) {
            $errors = SupportClass::getArrayWithErrors($favComp);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable unsubscribe user from company',
                    self::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_COMPANY, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable unsubscribe user from company',
                    self::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_COMPANY);
            }
        }
    }*/

    /*public function subscribeToUser(int $userId, int $userIdObject)
    {
        $fav = new FavoriteUsers();
        $fav->setSubjectId($userId);
        $fav->setObjectId($userIdObject);

        if (!$fav->create()) {
            SupportClass::getErrorsWithException($fav, self::ERROR_UNABLE_SUBSCRIBE_USER_TO_USER, 'Unable subscribe user to user');
        }
    }

    public function getSigningToUser(int $userId, int $userIdObject)
    {
        $fav = FavoriteUsers::findByIds('App\Models\FavoriteUsers', $userIdObject, $userId);

        if (!$fav) {
            throw new ServiceException('User don\'t subscribed to user', self::ERROR_USER_NOT_SUBSCRIBED_TO_USER);
        }
        return $fav;
    }

    public function unsubscribeFromUser(FavoriteUsers $favUser)
    {
        if (!$favUser->delete()) {
            SupportClass::getErrorsWithException($favUser,
                self::ERROR_UNABLE_UNSUBSCRIBE_USER_FROM_USER, 'Unable unsubscribe user from user');
        }
    }*/
}
