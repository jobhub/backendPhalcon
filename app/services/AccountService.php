<?php

namespace App\Services;

use App\Models\Accounts;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use App\Libs\SupportClass;
//Models
use App\Models\Users;
use App\Models\Phones;

/**
 * business and other logic for authentication. Maybe just creation simple objects.
 *
 * Class UsersService
 */
class AccountService extends AbstractService {

    const ADDED_CODE_NUMBER = 1000;

    const ERROR_UNABLE_CREATE_ACCOUNT = 1 + self::ADDED_CODE_NUMBER;
    /**
     * create account
     *
     * @param array $accountData [userId, companyId = null, company_role_id = null]
     * @return int. Return id of account.
     */
    public function createAccount(array $accountData) {
        $account = new Accounts();
        $account
            ->setUserId($accountData['userId'])
            ->setCompanyId($accountData['companyId'])
            ->setCompanyRoleId($accountData['company_role_id']);

        if ($account->save() == false) {
            $errors = SupportClass::getArrayWithErrors($account);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable to create account',
                    self::ERROR_UNABLE_CREATE_ACCOUNT,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable to create account',
                    self::ERROR_UNABLE_CREATE_ACCOUNT);
            }
        }

        return $account->getId();
    }
}
