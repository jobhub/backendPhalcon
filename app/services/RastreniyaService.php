<?php

namespace App\Services;


use App\Controllers\AbstractHttpException;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Libs\SupportClass;
use App\Models\Accounts;
use App\Models\Groups;
use App\Models\ImagesRastreniya;
use App\Models\Rastreniya;
use App\Models\RastreniyaResponses;
use App\Models\UserChatGroups;

use App\Models\Userinfo;
use App\Models\Users;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class RastreniyaService extends AbstractService
{
    const ADDED_CODE_NUMBER = 25000;

    const ERROR_TRANSACTION = 1 + self::ADDED_CODE_NUMBER;

    const ERROR_UNABLE_TO_ACCESS_GROUP = 5 + self::ADDED_CODE_NUMBER;

    public function create($user_id, $data, $file = null)
    {
        $content = $data["content"];
        $is_incognito = $data["is_incognito"];
        /*
         * start validation bloc
         */
        if (is_null($content) || strlen(trim($content)) == 0) {
            throw new Http400Exception(_('Missing content'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        if (is_null($is_incognito) || !is_bool($is_incognito)) {
            $is_incognito = false;
            //throw new Http400Exception(_('Wrong data : Missing is_incognito'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        /*if (!Users::isUserExist($user_id)) {
            throw new Http400Exception(_('User not found'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }*/

        if (!isset($data["account_id"]) && !Accounts::checkUserHavePermission($user_id, $data["account_id"])) {
            throw new Http400Exception(_('Unable to access to this account'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        /*
         * End validation block
         */
        try {
            $this->db->begin();
            /*$manager = new TxManager();
            $transaction = $manager->get();*/

            $userinfo = Userinfo::findFirstByUserId($user_id);

            $rast = new Rastreniya();
            //$rast->setTransaction($transaction);
            $rast->setUserId($user_id);
            $rast->setContent($content);
            $rast->setIsIncognito($is_incognito);
            $rast->setAccountId($data["account_id"]);
            $rast->setCityId($userinfo->getCityId());

            if($file != null)
                $rast->setHasAttachedFiles(true);

            if ($rast->save() === false) {
                /*$transaction->rollback(
                    'Cannot save Rast'
                );*/
                $this->db->rollback();
                throw new TxFailed('Cannot save Rast');
            }

            $ids = $this->imageService->createImagesToObject($this->request->getUploadedFiles(), $rast,
                ImageService::TYPE_RASTRENIYA);

            $this->imageService->saveImagesToObject($this->request->getUploadedFiles(), $rast,
                $ids, ImageService::TYPE_RASTRENIYA);

            //$transaction->commit();
            $this->db->commit();

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return self::getRastFromId($user_id, $rast->getId());
    }

    public function updateRast($data)
    {
        $rast_id = $data["rast_id"];
        $user_id = $data["user_id"];

        /*
         * start validation bloc
         */
        if (is_null($data["content"]) || strlen(trim($data["content"])) == 0) {
            throw new Http400Exception(_('Missing content'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        if (is_null($data["is_incognito"]) || !is_bool($data["is_incognito"])) {
            throw new Http400Exception(_('Wrong data : Missing is_incognito'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        if (!isset($data["account_id"]) && !Accounts::checkUserHavePermission($user_id, $data["account_id"])) {
            throw new Http403Exception(_('Unable to access to this account'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        $is_incognito = $data["is_incognito"];
        $content = $data["content"];
        $account_id = $data["account_id"];

        $rast = Rastreniya::findFirst([
            'conditions' => 'id = :rast_id: AND account_id = :account_id:',
            'bind' => [
                'rast_id' => $rast_id,
                'account_id' => $account_id
            ],
        ]);

        if (!$rast) {
            throw new Http400Exception(_('Unable to delete Rastreniya'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        /*
         * End validation block
         */
        try {
            $manager = new TxManager();
            $transaction = $manager->get();

            $rast->setTransaction($transaction);
            $rast->setContent($content);
            $rast->setIsIncognito($is_incognito);

            if ($rast->update() === false) {
                $transaction->rollback(
                    'Cannot update Rast'
                );
            }

            $transaction->commit();

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return $rast->getPublicInfo();
    }


    /**
     * Get Rastreniya from id
     *
     * @param $user_id int
     * @param $rast_id
     * @return array
     */
    public function getRastFromId($user_id, $rast_id)
    {
        try {
            $rast = Rastreniya::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    "id" => $rast_id
                ]
            ]);
            /*$account = Accounts::findFirst($rast->getAccountId());
            if (!$account) {
                $user = [];
            } else {
                $user = $account->getUserInfomations();
            }
            $likes = SupportClass::to_php_array($rast->getLikeUsers());
            $dislikes = SupportClass::to_php_array($rast->getDislikeUsers());
            $item = ['infos' => $rast->getPublicInfo()];
            //$item['owner'] = $user;
            $item['owner'] = $account->getUserInfomations();
            $item['likes'] = sizeof($likes);
            $item['dislikes'] = sizeof($dislikes);
            $item['comments'] = self::countComments($rast->getId());
            if (in_array($user_id, $likes)) {
                $item['is_liked'] = true;
            } else if (in_array($user_id, $dislikes)) {
                $item['is_disliked'] = true;
            }
            if ($item['comments']['total'] > 0) {
                // Load last comments info;
                $last = RastreniyaResponses::findFirst([
                    'conditions' => 'rastreniya_id = :rast_id:',
                    'bind' => [
                        'rast_id' => $rast->getId()
                    ],
                    'order' => 'create_at DESC',
                    'columns' => RastreniyaResponses::PUBLIC_COLUMNS
                ]);
                if ($account->getId() == $last['account_id'])
                    $owner = $user;
                else
                    $owner = Accounts::findFirst($rast->getAccountId())->getUserInfomations();
                $item['comments']['last_comment'] = $last;
                $item['comments']['user_info'] = $owner;
            }*/
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $this->handleRast($rast,$user_id);
    }


    /**
     * Get all Rastreniya
     *
     * @param $user_id int
     * @param $data array
     * @return array
     */
    public function getRasts($user_id, $data)
    {
        $page = 0;
        if (isset($data["page"]))
            $page = $data["page"];

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * Rastreniya::DEFAULT_RESULT_PER_PAGE;
        try {

            $userinfo = Userinfo::findFirstByUserId($user_id);

            $rasts = Rastreniya::find([
                'conditions' => 'city_id = :city_id:',
                'bind' => [
                    "city_id" => $userinfo->getCityId()
                ],
                'limit' => Rastreniya::DEFAULT_RESULT_PER_PAGE,
                'order' => 'create_at DESC',
                'offset' => $offset, // offset of result
            ]);
            $toRet = [];
            foreach ($rasts as $rast) {

                array_push($toRet, $this->handleRast($rast,$user_id));
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $toRet;
    }

    private function handleRast(Rastreniya $rast, $user_id){
        $account = Accounts::findFirst($rast->getAccountId());
        if (!$account) {
            $user = [];
        } else {
            $user = $account->getUserInfomations();
        }
        $likes = SupportClass::to_php_array($rast->getLikeUsers());
        $dislikes = SupportClass::to_php_array($rast->getDislikeUsers());
        $item = array();
        $item['infos'] =  $rast->getPublicInfo();
        //$item['owner'] = $user;
        $item['owner'] = $user;
        $item['likes'] = sizeof($likes);
        $item['dislikes'] = sizeof($dislikes);
        $item['comments'] = self::countComments($rast->getId());

        if($rast->getHasAttachedFiles())
            $item['infos']['image'] = ImagesRastreniya::findFirstByObjectId($rast->getId())->getImagePath();
        if (in_array($user_id, $likes)) {
            $item['infos']['is_liked'] = true;
        } else if (in_array($user_id, $dislikes)) {
            $item['infos']['is_disliked'] = true;
        }
        if ($item['comments']['total'] > 0) {
            // Load last comments info;
            $last = RastreniyaResponses::findFirst([
                'conditions' => 'rastreniya_id = :rast_id:',
                'bind' => [
                    'rast_id' => $rast->getId()
                ],
                'order' => 'create_at DESC',
                'columns' => RastreniyaResponses::PUBLIC_COLUMNS
            ]);
            if ($account->getId() == $last['account_id'])
                $owner = $user;
            else
                $owner = Accounts::findFirst($rast->getAccountId())->getUserInfomations();
            $item['comments']['last_comment'] = $last;
            $item['comments']['user_info'] = $owner;
        }
        return $item;
    }
    /**
     * likeRast
     *
     * @param $data
     * @return bool
     */
    public function likeRast($data)
    {
        try {
            if (!isset($data["rast_id"]) || !is_integer($data["rast_id"])) {
                throw new Http400Exception(_('Missing rastreniya id'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $rast_id = $data["rast_id"];
            $user_id = $data["user_id"];
            $this->log('' . $user_id);
            if (!Users::isUserExist($user_id)) {
                throw new Http400Exception(_('User not found'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $rast = Rastreniya::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $rast_id
                ]
            ]);
            if (!$rast)
                throw new Http400Exception(_('Unable to access to the rastreniya'), AbstractHttpException::BAD_REQUEST_CONTENT);

            $action = $rast->like($user_id);

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return ["is_liked" => $action];
    }

    /**
     * likeRast
     *
     * @param $data
     * @return bool
     */
    public function dislikeRast($data)
    {
        try {
            if (!isset($data["rast_id"]) || !is_integer($data["rast_id"])) {
                throw new Http400Exception(_('Missing rastreniya id'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $rast_id = $data["rast_id"];
            $user_id = $data["user_id"];
            if (!Users::isUserExist($user_id)) {
                throw new Http400Exception(_('User not found'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $rast = Rastreniya::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $rast_id
                ]
            ]);
            if (!$rast)
                throw new Http400Exception(_('Unable to access to the rastreniya'), AbstractHttpException::BAD_REQUEST_CONTENT);

            $action = $rast->dislike($user_id);

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return ["is_disliked" => $action];
    }


    /**
     * Add response to a Rastreniya
     *
     * @param $data
     * @return bool
     */
    public function newResponse($data)
    {
        $user_id = $data["user_id"];

        /*
         * Validation
         */
        if (!isset($data["account_id"]) && !Accounts::checkUserHavePermission($user_id, $data["account_id"])) {
            throw new Http400Exception(_('Unable to access to this account'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        if (!isset($data["content"]) || strlen(trim($data["content"])) == 0) {
            throw new Http400Exception(_('Missing content'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        if (!isset($data["rast_id"]) || !is_integer($data["rast_id"])) {
            throw new Http400Exception(_('Missing rastreniya id'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        /*
        * End Validation
        */

        $rast_id = $data["rast_id"];
        $account_id = $data["account_id"];
        $content = $data["content"];

        try {
            $manager = new TxManager();
            $transaction = $manager->get();

            $rast = Rastreniya::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $rast_id
                ]
            ]);
            if (!$rast)
                throw new Http400Exception(_('Unable to access to the rastreniya'), AbstractHttpException::BAD_REQUEST_CONTENT);

            $response = new RastreniyaResponses();
            $response->setTransaction($transaction);

            $response->setContent($content);
            $response->setAccountId($account_id);
            $response->setRastreniyaId($rast_id);

            if (isset($data["parent_id"]) && is_integer($data["parent_id"])) {
                $parent_id = $data["parent_id"];
                $parent = RastreniyaResponses::findFirst([
                    'conditions' => ' id = :id: AND rastreniya_id = :rast_id:',
                    'bind' => [
                        'rast_id' => $rast->getId(),
                        'id' => $parent_id
                    ]
                ]);

                if (!$parent) {
                    $this->log('Unable to set the parent ' . $parent_id . ' of a response in rastreniya id = ' . $rast_id);
                } else {
                    if ($parent->getParentId() != null)
                        $response->setParentId($parent->getParentId());
                    else
                        $response->setParentId($parent_id);
                }
            }


            if ($response->save() === false) {
                $transaction->rollback(
                    'Cannot save Rastreniya'
                );
            }
            $transaction->commit();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return self::getResponseById($response->getId());
    }

    /**
     * Add response to a Rastreniya
     *
     * @param $data
     * @return bool
     */
    public function updateResponse($data)
    {
        $user_id = $data["user_id"];
        if (!isset($data["account_id"]) && !Accounts::checkUserHavePermission($user_id, $data["account_id"])) {
            throw new Http400Exception(_('Unable to access to this account'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        if (is_null($data["content"]) || strlen(trim($data["content"])) == 0) {
            throw new Http400Exception(_('Missing content'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        if (is_null($data["response_id"]) || strlen(trim($data["response_id"])) == 0) {
            throw new Http400Exception(_('Missing content'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        $response_id = $data["response_id"];
        $account_id = $data["account_id"];
        $content = $data["content"];

        try {
            $manager = new TxManager();
            $transaction = $manager->get();


            $response = RastreniyaResponses::findFirst([
                'conditions' => ' id = :id: AND account_id = :account_id:',
                'bind' => [
                    'account_id' => $account_id,
                    'id' => $response_id
                ]
            ]);
            $response->setTransaction($transaction);

            $response->setContent($content);

            if ($response->update() === false) {
                $transaction->rollback(
                    'Cannot Update Rastreniya response'
                );
            }
            $transaction->commit();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return self::getResponseById($response->getId());
    }


    /**
     * get all response of a Rastreniya
     *
     * @param $data
     * @return bool
     */
    public function getResponses($data)
    {
        $rast_id = $data["rast_id"];
        $user_id = $data["user_id"];
        $content = $data["content"];
        if (isset($data["page"]) && is_integer($data["page"]))
            $page = $data["page"];
        else
            $page = 1;
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * RastreniyaResponses::DEFAULT_RESULT_PER_PAGE;
        if (!isset($data["rast_id"]) || !is_integer($data["rast_id"])) {
            throw new Http400Exception(_('Missing rastreniya id'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        try {
            /*if (!Users::isUserExist($user_id)) {
                throw new Http400Exception(_('User not found'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }*/

            $rast = Rastreniya::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $rast_id
                ]
            ]);
            if (!$rast)
                throw new Http400Exception(_('Unable to access to the rastreniya'), AbstractHttpException::BAD_REQUEST_CONTENT);

            // Load last comments info;
            $responses = RastreniyaResponses::find([
                'conditions' => 'rastreniya_id = :rast_id: AND parent_id IS NULL ',
                'bind' => [
                    'rast_id' => $rast->getId()
                ],
                'limit' => RastreniyaResponses::DEFAULT_RESULT_PER_PAGE,
                'order' => 'create_at ASC',
                'offset' => $offset, // offset of result
            ]);
            $toRet = [];
            foreach ($responses as $resp) {
                $item = self::getFormattedDataOfResponse($resp, $rast->getId());
                array_push($toRet, $item);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return $toRet;
    }


    /**
     * get all response of a Rastreniya
     *
     * @param $data
     * @return bool
     */
    public function getResponseById($resp_id)
    {
        try {
            // Load last comments info;
            $resp = RastreniyaResponses::findFirst([
                'conditions' => 'id = :resp_id:',
                'bind' => [
                    'resp_id' => $resp_id
                ],
            ]);
            $toRet = self::getFormattedDataOfResponse($resp);
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        return $toRet;
    }

    /**
     * Get all responses of response
     *
     * @param $response_id
     * @param $rast_id
     * @return array
     */
    public function getChildResponse($response_id, $rast_id)
    {
        // Load last comments info;
        $responses = RastreniyaResponses::find([
            'conditions' => 'rastreniya_id = :rast_id: AND parent_id = :parent_id: ',
            'bind' => [
                'rast_id' => $rast_id,
                'parent_id' => $response_id,
            ],
            //'limit' => RastreniyaResponses::DEFAULT_RESULT_PER_PAGE,
            'order' => 'create_at DESC',
            //'offset' => $offset, // offset of result
        ]);
        $toRet = [];
        foreach ($responses as $resp) {
            $item = self::getFormattedDataOfResponse($resp);
            array_push($toRet, $item);
        }
        return $toRet;
    }

    /**
     * likeRast
     *
     * @param $data
     * @return bool
     */
    public function deleteResponses($data)
    {
        $user_id = $data["user_id"];
        if (!isset($data["account_id"]) && !Accounts::checkUserHavePermission($user_id, $data["account_id"])) {
            throw new Http400Exception(_('Unable to access to this account'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }
        $response_id = $data["response_id"];
        $account_id = $data["account_id"];

        if (!Accounts::checkUserHavePermission($user_id, $account_id, 'deleteComment')) {
            throw new Http403Exception('Permission error');
        }

        if (!isset($response_id) || !is_integer($response_id)) {
            throw new Http400Exception(_('Missing response id'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        try {
            // Load last comments info;
            $response = RastreniyaResponses::findFirst([
                'conditions' => 'id = :resp_id: AND account_id = :account_id:',
                'bind' => [
                    'resp_id' => $response_id,
                    'account_id' => $account_id
                ],
            ]);
            if (!$response) {
                throw new Http400Exception(_('Unable to delete response'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }
            $response->delete();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return true;
    }

    /**
     * likeRast
     *
     * @param $data
     * @return bool
     */
    public function deleteRast($data)
    {
        $rast_id = $data["rast_id"];
        $user_id = $data["user_id"];

        if (!isset($data["account_id"]) && !Accounts::checkUserHavePermission($user_id, $data["account_id"])) {
            throw new Http400Exception(_('Unable to access to this account'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        $account_id = $data["account_id"];

        if (!isset($rast_id) || !is_integer($rast_id)) {
            throw new Http400Exception(_('Missing rast id'), AbstractHttpException::BAD_REQUEST_CONTENT);
        }

        if (!Accounts::checkUserHavePermission($user_id, $account_id, 'deleteComment')) {
            throw new Http403Exception('Permission error');
        }

        try {
            // Load last comments info;
            $rast = Rastreniya::findFirst([
                'conditions' => 'id = :rast_id: AND user_id = :user_id:',
                'bind' => [
                    'rast_id' => $rast_id,
                    'user_id' => $user_id
                ],
            ]);

            if (!$rast) {
                throw new Http400Exception(_('Unable to delete Rastreniya'), AbstractHttpException::BAD_REQUEST_CONTENT);
            }

            $rast->delete();
            $this->log('Deletion of rastreniya ' . $rast_id . '  by user ' . $user_id);
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        } catch (TxFailed $e) {
            throw new ServiceException('Failed, reason: ' . $e->getMessage(), self::ERROR_TRANSACTION, $e);
        }
        return true;
    }

    public function countComments($rast_id)
    {
        return $this->db->fetchOne('SELECT COUNT(*) AS total FROM rastreniya_responses WHERE deleted != TRUE AND rastreniya_id = ' . $rast_id);
    }

    // Helpfull functions

    private function getFormattedDataOfResponse($resp, $rast_id = null)
    {
        $item = ['info' => $resp->getPublicInfo()];
        $account = Accounts::findFirst($resp->getAccountId());

        if (!$account) {
            $owner = [];
        } else {
            $owner = $account->getUserInfomations();
        }

        $item['user_info'] = $owner;
        if ($rast_id != null)
            $item['childs'] = self::getChildResponse($resp->getId(), $rast_id);

        return $item;
    }

    private function getFormattedDataOfRast(Rastreniya $rast, $user_id)
    {
        $item['infos'] = $rast->getPublicInfo();

        if(!$rast->isIncognito()){
            $account = Accounts::findFirst($rast->getAccountId());

            if (!$account) {
                $user = [];
            } else {
                $user = $account->getUserInfomations();
            }
            $item['owner'] = $user;
        }
        $likes = SupportClass::to_php_array($rast->getLikeUsers());
        $dislikes = SupportClass::to_php_array($rast->getDislikeUsers());
        //$item['owner'] = $user;

        $item['likes'] = sizeof($likes);
        $item['dislikes'] = sizeof($dislikes);
        $item['comments'] = self::countComments($rast->getId());
        if (in_array($user_id, $likes)) {
            $item['infos']['is_liked'] = true;
        } else if (in_array($user_id, $dislikes)) {
            $item['infos']['is_disliked'] = true;
        }
        if ($item['comments']['total'] > 0) {
            // Load last comments info;
            $last = RastreniyaResponses::findFirst([
                'conditions' => 'rastreniya_id = :rast_id:',
                'bind' => [
                    'rast_id' => $rast->getId()
                ],
                'order' => 'create_at DESC',
                'columns' => RastreniyaResponses::PUBLIC_COLUMNS
            ]);
            /*if ($account->getId() == $last['account_id'])
                $owner = $user;
            else*/
            $owner = Accounts::findFirst($rast->getAccountId())->getUserInfomations();
            $item['comments']['last_comment'] = $last;
            $item['comments']['user_info'] = $owner;
        }
        return $item;
    }

}