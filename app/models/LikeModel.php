<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 23.01.2019
 * Time: 14:29
 */

namespace App\Models;

use App\Libs\SupportClass;
use Phalcon\DI\FactoryDefault as DI;

class LikeModel
{
    /**
     * @param $model
     * @param $name_id
     * @param $object_id
     * @param Accounts $account
     * @return bool
     */
    public static function getObjectLikedByAccount($model, $name_id, $object_id, Accounts $account): bool
    {
        if ($account->getCompanyId() == null) {
            $accounts = [$account->getId()];
        } else {
            $accounts_obj = Accounts::findByCompanyId($account->getCompanyId());
            $accounts = [];
            foreach ($accounts_obj as $account) {
                $accounts[] = $account->getId();
            }
        }

        $db = DI::getDefault()->getDb();
        $query = $db->prepare('select 1 from public.' . $model::getSource()
            . ' where ' . $name_id . ' = :object_id and likes && :accounts'
        );

        $query->execute([
            'accounts' => SupportClass::to_pg_array($accounts),
            'object_id' => $object_id,
        ]);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);

        return count($result) != 0;
    }

    public static function getLikedByArray(array $likes, string $relatedAccounts): bool
    {
        $intersect = array_intersect($likes,SupportClass::translateInPhpArrFromPostgreArr($relatedAccounts));

        return count($intersect) != 0;
    }

    public static function handleObjectWithLikes(array $handledObject, array $object, $accountId = null, $relatedAccounts = null){

        if(is_array($object['likes']))
            $likes = $object['likes'];
        else
            $likes = SupportClass::translateInPhpArrFromPostgreArr($object['likes']);
        if(is_null($likes))
            $handledObject['stats']['likes'] = 0;
        else
            $handledObject['stats']['likes'] = count($likes);

        if($accountId!=null && SupportClass::checkInteger($accountId)){
            if($relatedAccounts!=null)
                $handledObject['liked'] = LikeModel::getLikedByArray($likes,$relatedAccounts);
            else{
                if($accountId!=null){
                    $account = Accounts::findAccountById($accountId);
                    if($account){
                        $handledObject['liked'] = LikeModel::getLikedByArray($likes,$account->getRelatedAccounts());
                    }
                }
            }
        }

        return $handledObject;
    }
}