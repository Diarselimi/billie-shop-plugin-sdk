<?php

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\DomainModel\Order\OrderRegistrationNumberConverter;
use Symfony\Component\HttpFoundation\Request;

class DebtorRequestFactory
{
    private OrderRegistrationNumberConverter $registrationNumberConverter;

    private AddressRequestFactory $addressRequestFactory;

    public function __construct(
        OrderRegistrationNumberConverter $registrationNumberConverter,
        AddressRequestFactory $addressRequestFactory
    ) {
        $this->registrationNumberConverter = $registrationNumberConverter;
        $this->addressRequestFactory = $addressRequestFactory;
    }

    public function create(Request $request): CreateOrderDebtorCompanyRequest
    {
        return $this->doCreate($request)
            ->setAddress($this->addressRequestFactory->createFromArray($request->request->get('debtor_company')['address']))
        ;
    }

    public function createForLegacyOrder(Request $request): CreateOrderDebtorCompanyRequest
    {
        return $this->doCreate($request)
            ->setAddress($this->addressRequestFactory->createFromOldFormat($request->request->get('debtor_company')))
        ;
    }

    private function doCreate(Request $request): CreateOrderDebtorCompanyRequest
    {
        $requestData = $request->request->get('debtor_company');

        $debtorCompany = (new CreateOrderDebtorCompanyRequest())
            ->setMerchantCustomerId($requestData['merchant_customer_id'] ?? null)
            ->setName($requestData['name'] ?? null)
            ->setTaxId($requestData['tax_id'] ?? null)
            ->setTaxNumber($requestData['tax_number'] ?? null)
            ->setRegistrationCourt($requestData['registration_court'] ?? null)
            ->setIndustrySector($requestData['industry_sector'] ?? null)
            ->setSubindustrySector($requestData['subindustry_sector'] ?? null)
            ->setEmployeesNumber($requestData['employees_number'] ?? null)
            ->setLegalForm($requestData['legal_form'] ?? null)
            ->setEstablishedCustomer((bool) $requestData['established_customer'] ?? null);

        if ($requestData['registration_number'] ?? false) {
            $debtorCompany->setRegistrationNumber(
                $this->registrationNumberConverter->convert(
                    $requestData['registration_number'],
                    $requestData['registration_court'] ?? ''
                )
            );
        }

        return $debtorCompany;
    }
}
