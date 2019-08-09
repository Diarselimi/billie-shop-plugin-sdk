<?php

namespace App\DomainModel\CheckoutSessionResponse;

use App\DomainModel\Order\OrderContainer\OrderContainer;

class AuthorizeOrderResponseFactory
{
    public function create(OrderContainer $orderContainer): AuthorizeOrderResponse
    {
        $response = (new AuthorizeOrderResponse())
            ->setCompanyName($orderContainer->getDebtorCompany()->getName())
            ->setCompanyAddressHouseNumber($orderContainer->getDebtorCompany()->getAddressHouse())
            ->setCompanyAddressPostalCode($orderContainer->getDebtorCompany()->getAddressPostalCode())
            ->setCompanyAddressStreet($orderContainer->getDebtorCompany()->getAddressStreet())
            ->setCompanyAddressCity($orderContainer->getDebtorCompany()->getAddressCity())
            ->setCompanyAddressCountry($orderContainer->getDebtorCompany()->getAddressCountry())
            ;

        return $response;
    }
}
