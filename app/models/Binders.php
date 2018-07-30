<?php

class Binders
{
    public static function checkUserHavePermission($userId, $binderId, $binderType, $executor, $right = null)
    {
        $user = Users::findFirstByUserId($userId);

        if (!$user)
            return false;

        if ($binderType == 0) {
            //связующий объект - заказ (task)

            $task = Tasks::findFirstByTaskId($binderId);
            if(!$task)
                return false;

            if($executor)
            {
                $binder = Offers::findFirst(['taskId = :taskId: AND selected = true',
                    'bind' => ['taskId' => $task->getTaskId()]]);
                if(!$binder){
                    return false;
                }
            } else{
                $binder = $task;
            }
            return SubjectsWithNotDeleted::checkUserHavePermission($userId, $binder->getSubjectId(),$binder->getSubjectType(),$right);
        } else if ($binderType == 1) {
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

            return SubjectsWithNotDeleted::checkUserHavePermission($userId, $binder->getSubjectId(),$binder->getSubjectType(),$right);
        }

        return false;
    }

    public static function checkBinderExists($binderId, $binderType)
    {
        if ($binderType == 0) {
            $task = Tasks::findFirstByTaskId($binderId);
            if (!$task)
                return false;
            $offer = Offers::findFirst(['taskId = :taskId: AND selected = true',
                'bind' => ['taskId' => $task->getTaskId()]]);
            if (!$offer)
                return false;
            return true;
        } else if ($binderType == 1) {
            $request = Requests::findFirstByRequestId($binderId);
            if ($request && $request->services)
                return true;
            return false;
        } else
            return false;
    }

}