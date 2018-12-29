<?php

namespace App\Services;

use App\Libs\SupportClass;

use App\Models\FavoriteCategories;

/**
 * business logic for users
 *
 * Class UsersService
 */
class CategoryService extends AbstractService
{
    const ADDED_CODE_NUMBER = 6000;

    const ERROR_ALREADY_SIGNED = 1 + self::ADDED_CODE_NUMBER;

    public function setFavourite(int $userId, int $categoryId, double $radius)
    {
        $fav = FavoriteCategories::findByIds($userId, $categoryId);
        if ($fav) {
            throw new ServiceException('User already signed on this category',
                self::ERROR_ALREADY_SIGNED);
        }

        $fav = new FavoriteCategories();
        $fav->setCategoryId($categoryId);
        $fav->setUserId($userId);
        $fav->setRadius($radius);

        if (!$fav->save()) {
            $errors = [];
            foreach ($fav->getMessages() as $message) {
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

        $response->setJsonContent(
            [
                "status" => STATUS_OK,
            ]
        );
        return $response;
    }

    /**
     * Creating a new user
     *
     * @param array $userData
     * @return Users. If all ok, return Users object
     */
    public function createUser(array $userData)
    {
        try {
            $user = new Users();

            if (Phones::isValidPhone($userData['login'])) {
                $result = $this->phoneService->createPhone($userData['login']);
                if ($result['status'] != STATUS_OK)
                    return $result;

            } else {
                $user->setEmail($userData['login']);
            }

            $user->setPassword($userData['password']);
            $user->setRole(ROLE_GUEST);
            $user->setIsSocial(false);
            $user->setActivated(false);

            if ($user->save() == false) {
                $errors = SupportClass::getArrayWithErrors($user);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('unable to create user',
                        self::ERROR_UNABLE_CREATE_USER, null, null, $errors);
                else {
                    throw new ServiceExtendedException('unable to create user',
                        self::ERROR_UNABLE_CREATE_USER);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $user;
    }

    public function changeUser(Users $user, array $userData)
    {
        if (!empty(trim($userData['email']))) {
            $user->setEmail($userData['email']);
        }

        if (!empty(trim($userData['phoneId']))) {
            $user->setPhoneId($userData['phoneId']);
        }

        if (!empty(trim($userData['role']))) {
            $user->setRole($userData['role']);
        }

        if (!empty(trim($userData['password']))) {
            $user->setPassword($userData['password']);
        }

        if (!empty(trim($userData['activated']))) {
            $user->setActivated($userData['activated']);
        }

        if (!empty(trim($userData['isSocial']))) {
            $user->setIsSocial($userData['isSocial']);
        }

        if (!$user->update()) {
            $errors = SupportClass::getArrayWithErrors($user);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to update user',
                    self::ERROR_UNABLE_CHANGE_USER, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to update user',
                    self::ERROR_UNABLE_CHANGE_USER);
            }
        }
    }

    /**
     * Delete an existing user
     *
     * @param int $userId
     */
    public function deleteUser($userId)
    {
        try {
            $user = Users::findFirstByUserid($userId);

            if (!$user) {
                throw new ServiceException("User not found", self::ERROR_USER_NOT_FOUND);
            }

            $result = $user->delete();

            if (!$result) {
                $errors = SupportClass::getArrayWithErrors($user);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to delete user',
                        self::ERROR_UNABLE_DELETE_USER, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to delete user',
                        self::ERROR_UNABLE_DELETE_USER);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
