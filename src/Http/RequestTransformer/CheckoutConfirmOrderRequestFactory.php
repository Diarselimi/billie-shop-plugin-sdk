<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer;

use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderRequest;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmDebtorCompanyRequest;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmDebtorCompanyRequestLegacy;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\CreateOrder\AddressRequestFactory;
use Symfony\Component\HttpFoundation\Request;

class CheckoutConfirmOrderRequestFactory
{
    private AmountRequestFactory $amountRequestFactory;

    private AddressRequestFactory $addressRequestFactory;

    public function __construct(
        AmountRequestFactory $amountRequestFactory,
        AddressRequestFactory $addressRequestFactory
    ) {
        $this->amountRequestFactory = $amountRequestFactory;
        $this->addressRequestFactory = $addressRequestFactory;
    }

    public function create(Request $request, string $sessionUuid): CheckoutConfirmOrderRequest
    {
        if ($this->isV2Request($request)) {
            $debtorCompanyData = $request->request->get('debtor');
            $externalCode = $request->request->get('external_code');
        } else {
            $debtorCompanyData = $request->request->get('debtor_company');
            $externalCode = $request->request->get('order_id');
        }

        $duration = $request->request->getInt('duration', 0);

        return (new CheckoutConfirmOrderRequest())
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setDebtorCompanyRequest($this->buildDebtorCompanyRequest($debtorCompanyData))
            ->setAmount($this->amountRequestFactory->create($request))
            ->setDeliveryAddress($this->addressRequestFactory->create($request, 'delivery_address'))
            ->setDuration($duration)
            ->setExternalCode($externalCode)
            ->setSessionUuid($sessionUuid);
    }

    private function buildDebtorCompanyRequest($requestData)
    {
        if (!is_array($requestData) || empty($requestData)) {
            return null;
        }

        if (isset($requestData['address_postal_code'])) {
            $addressRequest = $this->addressRequestFactory->createFromOldFormat($requestData);

            return (new CheckoutConfirmDebtorCompanyRequestLegacy())
                ->setName($requestData['name'] ?? null)
                ->setCompanyAddress($addressRequest);
        }

        $addressRequest = $this->addressRequestFactory->createFromArray($requestData['company_address']);

        return (new CheckoutConfirmDebtorCompanyRequest())
            ->setName($requestData['name'] ?? null)
            ->setCompanyAddress($addressRequest);
    }

    private function isV2Request(Request $request): bool
    {
        return strpos(strtolower($request->getUri()), 'v2') !== false || array_key_exists('debtor', $request->request->all());
    }
}
