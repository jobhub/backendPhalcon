<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 19.07.2018
 * Time: 11:07
 */

class Subjects
{
    public static function checkUserHavePermission($userId, $subjectId, $subjectType, $right = null)
    {
        $user = Users::findFirstByUserId($userId);

        if(!$user)
            return false;

        if ($subjectType == 1) {
            //субъект - компания

            if (!Companies::checkUserHavePermission($userId, $subjectId, $right)) {
                return false;
            }
            return true;
        } else if ($subjectType == 0) {
            //субъект - пользователь

            if ($subjectId != $userId && $user->getRole() != ROLE_MODERATOR) {
                return false;
            }

            return true;
        }

        return false;
    }

    public static function checkSubjectExists($subjectId, $subjectType)
    {
        if($subjectType == 0) {
            $user = Users::findFirstByUserId($subjectId);
            if ($user)
                return true;
            return false;
        } else if($subjectType == 1){
            $company = Companies::findFirstByCompanyId($subjectId);
            if ($company)
                return true;
            return false;
        } else
            return false;
    }
}