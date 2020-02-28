<?php

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;

class CheckoutConfirmOrderFactory
{
    public function create(
        array $amountData,
        array $debtorCompanyData,
        int $duration,
        string $sessionUuid
    ) {
        return (new CheckoutConfirmOrderRequest())
            ->setAmount(
                (new CreateOrderAmountRequest())
                ->setNet($amountData['net'])
                ->setGross($amountData['gross'])
                ->setTax($amountData['tax'])
            )
            ->setDebtorCompanyRequest($this->buildDebtorCompanyRequest($debtorCompanyData))
            ->setDuration($duration)
            ->setSessionUuid($sessionUuid);
    }

    private function buildDebtorCompanyRequest(array $requestData): DebtorCompanyRequest
    {
        return (new DebtorCompanyRequest())
            ->setName($requestData['name'] ?? null)
            ->setAddressAddition($requestData['address_addition'] ?? null)
            ->setAddressHouseNumber($requestData['address_house_number'] ?? null)
            ->setAddressStreet($requestData['address_street'] ?? null)
            ->setAddressCity($requestData['address_city'] ?? null)
            ->setAddressPostalCode($requestData['address_postal_code'] ?? null)
            ->setAddressCountry($requestData['address_country'] ?? null);
    }
}
