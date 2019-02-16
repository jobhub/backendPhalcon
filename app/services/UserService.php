<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class UserService extends AbstractService
{
    const ADDED_CODE_NUMBER = 26000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_USER = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_USER = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_USER_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_USER = 4 + self::ADDED_CODE_NUMBER;

    /**
     * Creating a new user
     *
     * @param array $userData
     */
    /*public function createUser(array $userData) {
        try {
            $user = new Users();
            $result = $user->setEmail($userData['email'])
                    ->setPassword(password_hash($userData['password'], PASSWORD_DEFAULT))
                    //->setLastName($userData['first_name'])
                    //->setFirstName($userData['last_name'])
                    ->setStatus($userData['status'])
                    ->create();

            if (!$result) {
                throw new ServiceException('Unable to create user', self::ERROR_UNABLE_CREATE_USER);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23505) {
                throw new ServiceException('User already exists', self::ERROR_ALREADY_EXISTS, $e);
            } else {
                throw new ServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }*/

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
            $user->setIsSocial(isset($userData['is_social'])?$userData['is_social']:false);
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

    /**
     * Setting a new password for user
     *
     * @param Users $user
     * @param string $password
     */
    public function setPasswordForUser(Users $user, string $password)
    {
        try {
            $user->setPassword($password);
            if ($user->update() == false) {
                $errors = SupportClass::getArrayWithErrors($user);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to change password of user',
                        self::ERROR_UNABLE_CHANGE_USER, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to change password of user',
                        self::ERROR_UNABLE_CHANGE_USER);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Setting a new role for user
     *
     * @param Users $user
     * @param string $role
     */
    public function setNewRoleForUser(Users $user, string $role)
    {
        try {
            $user->setRole($role);
            if ($user->update() == false) {
                $errors = SupportClass::getArrayWithErrors($user);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to change role of user',
                        self::ERROR_UNABLE_CHANGE_USER, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to change role of user',
                        self::ERROR_UNABLE_CHANGE_USER);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function changeUser(Users $user, array $userData){
        if(!empty(trim($userData['email']))){
            $user->setEmail($userData['email']);
        }

        if(!empty(trim($userData['phoneId']))){
            $user->setPhoneId($userData['phoneId']);
        }

        if(!empty(trim($userData['role']))){
            $user->setRole($userData['role']);
        }

        if(!empty(trim($userData['password']))){
            $user->setPassword($userData['password']);
        }

        if(!empty(trim($userData['activated']))){
            $user->setActivated($userData['activated']);
        }

        if(!empty(trim($userData['isSocial']))){
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
     * @param Users $user
     */
    public function deleteUser(Users $user)
    {
        try {
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

    public function getUserByLogin(string $login){
        $user = Users::findByLogin($login);

        if (!$user || $user == null) {
            throw new ServiceException('Invalid login', self::ERROR_USER_NOT_FOUND);
        }
        return $user;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getUserById(int $userId){
        $user = Users::findFirstByUserId($userId);

        if (!$user || $user == null) {
            throw new ServiceException('User don\'t exists', self::ERROR_USER_NOT_FOUND);
        }
        return $user;
    }
    /**
     * Updating an existing user
     *
     * @param array $userData
     */
    public function findOnByEmail($email)
    {
        try {
            $user = User::findFirst(
                [
                    'conditions' => 'email = :email:',
                    'bind' => [
                        'email' => $email
                    ],
                    'columns' => "id, email, first_name, last_name, lastconnexion, status",
                ]
            );

            if (!$user) {
                return [];
            }

            return $user->toArray();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function createGroup(array $data)
    {
        try {
            $group = new Group();
            $chatHis = $this->chatHistoryService->createChatHistory();
            $group->setName($data["name"]);
            $group->setChatHistId($chatHis->getId());
            $result = $group->save();
            $this->logger->critical(
                $result . '===' . $result
            );
            if (!$result) {
                throw new ServiceException('Unable to create Groupe', self::ERROR_UNABLE_CREATE_USER, '', $this->logger);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23505) {
                throw new ServiceException('User already exists', self::ERROR_ALREADY_EXISTS, $e, $this->logger);
            } else {
                throw new ServiceException($e->getMessage(), $e->getCode(), $e, $this->logger);
            }
        }
    }

    /**
     * Updating an existing user
     *
     * @param array $userData
     */
    public function updateUser(array $userData)
    {
        try {
            $user = Users::findFirst(
                [
                    'conditions' => 'id = :id:',
                    'bind' => [
                        'id' => $userData['id']
                    ]
                ]
            );

            $userData['email'] = (is_null($userData['email'])) ? $user->getemail() : $userData['email'];
            $userData['password'] = (is_null($userData['password'])) ? $user->getPass() : password_hash($userData['password'], PASSWORD_DEFAULT);
            $userData['first_name'] = (is_null($userData['first_name'])) ? $user->getFirstName() : $userData['first_name'];
            $userData['last_name'] = (is_null($userData['last_name'])) ? $user->getLastName() : $userData['last_name'];

            $result = $user->setemail($userData['email'])
                ->setPass($userData['password'])
                ->setFirstName($userData['first_name'])
                ->setLastName($userData['last_name'])
                ->update();

            if (!$result) {
                throw new ServiceException('Unable to update user', self::ERROR_UNABLE_UPDATE_USER);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete an existing user
     *
     * @param int $userId
     */
    /*public function deleteUser($userId) {
        try {
            $user = User::findFirst(
                            [
                                'conditions' => 'id = :id:',
                                'bind' => [
                                    'id' => $userId
                                ]
                            ]
            );

            if (!$user) {
                throw new ServiceException("User not found", self::ERROR_USER_NOT_FOUND);
            }

            $result = $user->delete();

            if (!$result) {
                throw new ServiceException('Unable to delete user', self::ERROR_UNABLE_DELETE_USER);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }*/


    /**
     * Returns user list
     *
     * @return array
     */
    public function getUserList($currentUserId)
    {
        try {

            $users = Users::find(
                [
                    'conditions' => 'userid != :id:',
                    'bind' => ['id' => $currentUserId],
                ], false
            );

            $this->logger->critical(
                ' Internal Server Error '
            );

            if (!$users) {
                return [];
            }

            return $users->toArray();
        } catch (\PDOException $e) {
            $this->logger->critical(
                $e->getMessage() . ' ' . $e->getCode()
            );
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

}
