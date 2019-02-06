<?php

namespace App\Services;

use App\Models\Offers;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class OfferService extends AbstractService
{
    const ADDED_CODE_NUMBER = 18000;

    const ERROR_UNABLE_CREATE_OFFER = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_OFFER_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_OFFER = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_OFFER = 4 + self::ADDED_CODE_NUMBER;


    public function createOffer(array $offerData)
    {
        $offer = new Offers();

        $this->fillOffer($offer, $offerData);

        if ($offer->create() == false) {
            SupportClass::getErrorsWithException($offer,self::ERROR_UNABLE_CREATE_OFFER,'Unable to create offer');
        }

        return $offer;
    }

    public function getOfferById(int $offerId)
    {
        $offer = Offers::findFirstByOfferId($offerId);

        if (!$offer) {
            throw new ServiceException('Offer don\'t exists', self::ERROR_OFFER_NOT_FOUND);
        }
        return $offer;
    }

    public function fillOffer(Offers $offer, array $data)
    {
        if (!empty(trim($data['task_id'])))
            $offer->setTaskId($data['task_id']);
        if (!empty(trim($data['description'])))
            $offer->setDescription($data['description']);
        if (!empty(trim($data['deadline'])))
            $offer->setDeadline(date('Y-m-d H:i:s', strtotime($data['deadline'])));
        if (!empty(trim($data['price'])))
            $offer->setPrice($data['price']);
        if (!empty(trim($data['selected'])))
            $offer->setSelected($data['selected']);
        if (!empty(trim($data['confirmed'])))
            $offer->setConfirmed($data['confirmed']);
        if (!empty(trim($data['account_id'])))
            $offer->setAccountId($data['account_id']);
    }

    public function deleteOffer(Offers $offer)
    {
        if ($offer->delete() == false) {
            SupportClass::getErrorsWithException($offer,self::ERROR_UNABLE_DELETE_OFFER,'Unable to delete offer');
        }

        return $offer;
    }

    public function changeOffer(Offers $offer, array $offerData)
    {
        $this->fillOffer($offer, $offerData);

        if ($offer->update() == false) {
            SupportClass::getErrorsWithException($offer,self::ERROR_UNABLE_CHANGE_OFFER,'Unable to change offer');
        }

        return $offer;
    }
}
