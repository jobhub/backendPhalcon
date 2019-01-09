<?php

namespace App\Services;

use App\Models\CompanyRole;
use Phalcon\DI\FactoryDefault as DI;

use App\Models\Companies;

use App\Libs\SupportClass;
use App\Libs\ImageLoader;
//models
use App\Models\Userinfo;
use App\Models\Settings;
use App\Controllers\HttpExceptions\Http500Exception;

/**
 * business logic for users
 *
 * Class UsersService
 */
class CompanyService extends AbstractService {

    const ADDED_CODE_NUMBER = 12000;

    /** Unable to create user */
    const ERROR_COMPANY_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_COMPANY = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_COMPANY = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CREATE_COMPANY = 4 + self::ADDED_CODE_NUMBER;

    public function createCompany(array $companyData, int $creator_user_id){
        $company = new Companies();

        $this->fillCompany($company,$companyData);

        if ($company->save() == false) {
            $errors = SupportClass::getArrayWithErrors($company);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable create company',
                    self::ERROR_UNABLE_CREATE_COMPANY,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable create company',
                    self::ERROR_UNABLE_CREATE_COMPANY);
            }
        }

        return $company;
    }

    public function changeCompany(Companies $company, array $companyData){
        $this->fillCompany($company,$companyData);
        if ($company->update() == false) {
            $errors = SupportClass::getArrayWithErrors($company);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable change company',
                    self::ERROR_UNABLE_CHANGE_COMPANY,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable change company',
                    self::ERROR_UNABLE_CHANGE_COMPANY);
            }
        }
        return $company;
    }

    private function fillCompany(Companies $company, array $data){
        if(!empty(trim($data['name'])))
            $company->setName($data['name']);
        if(!empty(trim($data['description'])))
            $company->setDescription($data['description']);
        if(!empty(trim($data['full_name'])))
            $company->setFullName($data['full_name']);
        if(!empty(trim($data['tin'])))
            $company->setTIN($data['tin']);
        if(!empty(trim($data['logotype'])))
            $company->setLogotype($data['logotype']);
        if(!empty(trim($data['region_id'])))
            $company->setRegionId($data['region_id']);
        if(!empty(trim($data['website'])))
            $company->setWebsite($data['website']);
        if(!empty(trim($data['email'])))
            $company->setEmail($data['email']);
        if(!empty(trim($data['is_master'])))
            $company->setIsMaster($data['is_master']);
        if(!empty(trim($data['rating_executor'])))
            $company->setRatingExecutor($data['rating_executor']);
        if(!empty(trim($data['rating_client'])))
            $company->setRatingClient($data['rating_client']);
    }

    public function getCompanyById(int $companyId){
        $company = Companies::findFirstByCompanyId($companyId);

        if (!$company || $company == null) {
            throw new ServiceException('Company don\'t exists', self::ERROR_COMPANY_NOT_FOUND);
        }
        return $company;
    }

    public function deleteCompany(Companies $company){
        try{
        if (!$company->delete()) {
            $errors = SupportClass::getArrayWithErrors($company);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to delete company',
                    self::ERROR_UNABLE_DELETE_COMPANY, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to delete company',
                    self::ERROR_UNABLE_DELETE_COMPANY);
            }
        }
        }catch(\PDOException $e){
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }
    }
}
