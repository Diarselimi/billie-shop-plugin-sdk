<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer;

use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderRequest;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use Symfony\Component\HttpFoundation\Request;

class CheckoutConfirmOrderRequestFactory
{
    private $amountRequestFactory;

    public function __construct(AmountRequestFactory $amountRequestFactory)
    {
        $this->amountRequestFactory = $amountRequestFactory;
    }

    public function create(Request $request, string $sessionUuid): CheckoutConfirmOrderRequest
    {
        $debtorCompanyData = $request->request->get('debtor_company', []);
        $duration = $request->request->getInt('duration', 0);

        return (new CheckoutConfirmOrderRequest())
            ->setAmount($this->amountRequestFactory->create($request))
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
