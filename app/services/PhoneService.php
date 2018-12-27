<?php

namespace App\Services;

//Models
use App\Models\Phones;

/**
 * business and other logic for authentication. Maybe just creation simple objects.
 *
 * Class UsersService
 */
class PhoneService extends AbstractService {

    const ERROR_UNABLE_CREATE_PHONE = 11001;
    /**
     * Create phone if it don't exists.
     *
     * @param $phone
     * @return array. If all ok - return id of new (or old) phone. Else return array of the errors.
     */
    public function createPhone($phone)
    {
        $phoneObject = new Phones();
        $phone = Phones::formatPhone($phone);
        $phoneObject->setPhone($phone);

        if ($phoneObject->save() == false) {
            $this->db->rollback();
            $errors = [];
            foreach ($phoneObject->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }
            return
                [
                    "status" => STATUS_WRONG,
                    "errors" => $errors
                ];
        }
        return [
            'status' => STATUS_OK,
            'data' => $phoneObject->getPhoneId()
            ];
    }
}
