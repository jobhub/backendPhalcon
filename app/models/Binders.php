<?php

namespace App\Models;

class Binders
{
    public static function checkUserHavePermission($userId, $binderId, $binderType, $executor, $right = null)
    {
        $user = Users::findFirstByUserId($userId);

        if (!$user)
            return false;

        if ($binderType == 'task') {
            //связующий объект - заказ (task)

            $task = Tasks::findFirstByTaskId($binderId);
            if(!$task)
                return false;

            if($executor)
            {
                $binder = Offers::findFirst(['task_id = :taskId: AND selected = true',
                    'bind' => ['taskId' => $task->getTaskId()]]);
                if(!$binder){
                    return false;
                }
            } else{
                $binder = $task;
            }

            return Accounts::checkUserHavePermission($userId, $binder->getAccountId(),$right);
        } else if ($binderType == 'request') {
            //связующий объект - заявка

            $request = Requests::findFirstByRequestId($binderId);
            if(!$request)
                return false;

            if($executor)
            {
                $binder = $request->services;
                if(!$binder){
                    return false;
                }
            } else{
                $binder = $request;
            }

            return Accounts::checkUserHavePermission($userId, $binder->getAccountId(),$right);
        }

        return false;
    }

    public static function checkBinderExists($binderId, $binderType)
    {
        if ($binderType == 'task') {
            $task = Tasks::findFirstByTaskId($binderId);
            if (!$task)
                return false;
            $offer = Offers::findFirst(['task_id = :taskId: AND selected = true',
                'bind' => ['taskId' => $task->getTaskId()]]);
            if (!$offer)
                return false;
            return true;
        } else if ($binderType == 'request') {
            $request = Requests::findFirstByRequestId($binderId);
            if ($request && $request->services)
                return true;
            return false;
        } else
            return false;
    }

    public static function getBinderByServiceType($binder_id, $type){
        if($type=='service')
            $binder = Services::findServiceById($binder_id);
        else if($type=='product')
            $binder = Products::findProductById($binder_id);

        return $binder;
    }
}