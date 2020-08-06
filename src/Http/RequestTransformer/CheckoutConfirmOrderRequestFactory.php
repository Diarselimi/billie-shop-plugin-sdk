<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer;

use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderRequest;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\CreateOrder\AddressRequestFactory;
use Symfony\Component\HttpFoundation\Request;

class CheckoutConfirmOrderRequestFactory
{
    private $amountRequestFactory;

    private $addressRequestFactory;

    public function __construct(
        AmountRequestFactory $amountRequestFactory,
        AddressRequestFactory $addressRequestFactory
    ) {
        $this->amountRequestFactory = $amountRequestFactory;
        $this->addressRequestFactory = $addressRequestFactory;
    }

    public function create(Request $request, string $sessionUuid): CheckoutConfirmOrderRequest
    {
        $debtorCompanyData = $request->request->get('debtor_company');
        $duration = $request->request->getInt('duration', 0);

        return (new CheckoutConfirmOrderRequest())
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setAmount($this->amountRequestFactory->create($request))
            ->setDebtorCompanyRequest($this->buildDebtorCompanyRequest($debtorCompanyData))
            ->setDeliveryAddress($this->addressRequestFactory->create($request, 'delivery_address'))
            ->setDuration($duration)
            ->setExternalCode($request->request->get('order_id'))
            ->setSessionUuid($sessionUuid);
    }

    private function buildDebtorCompanyRequest($requestData): ?DebtorCompanyRequest
    {
        if (!is_array($requestData) || empty($requestData)) {
            return null;
        }

        $addressRequest = $this->addressRequestFactory->createFromOldFormat($requestData);

        return (new DebtorCompanyRequest())
            ->setName($requestData['name'] ?? null)
            ->setAddressRequest($addressRequest);
    }
}
