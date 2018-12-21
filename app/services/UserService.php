<?php

namespace App\Services;

use App\Models\Users;
use App\Models\Group;

/**
 * business logic for users
 *
 * Class UsersService
 */
class UserService extends AbstractService {

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_USER = 11001;

    /**
     * Creating a new user
     *
     * @param array $userData
     */
    public function createUser(array $userData) {
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
    }

    /**
     * Updating an existing user
     *
     * @param array $userData
     */
    public function findOnByEmail($email) {
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

    public function createGroup(array $data) {
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
    public function updateUser(array $userData) {
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
    public function deleteUser($userId) {
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
    }

    /**
     * Returns user list
     *
     * @return array
     */
    public function getUserList($currentUserId) {
        try { 
            $users = Users::find(
                            [
                                'conditions' => 'id != :id:',
                                'bind' => ['id' => $currentUserId], // Must be change by the logged user
                                //'columns' => "id, email, first_name, last_name, lastconnexion, status",
                            ]
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
