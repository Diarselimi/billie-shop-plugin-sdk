<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateOrderController
{
    private $useCase;

    public function __construct(CreateOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request)
    {
        $request = (new CreateOrderRequest())
            ->setCustomerId($request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER))
            ->setAmountNet($request->request->get('amount_net'))
            ->setAmountGross($request->request->get('amount_gross'))
            ->setAmountTax($request->request->get('amount_tax'))
            ->setDuration($request->request->get('duration'))
            ->setComment($request->request->get('comment'))
            ->setExternalCode($request->request->get('external_code'))

            ->setDeliveryAddressHouseNumber($request->request->get('delivery_address_house_number'))
            ->setDeliveryAddressStreet($request->request->get('delivery_address_street'))
            ->setDeliveryAddressPostalCode($request->request->get('delivery_address_postal_code'))
            ->setDeliveryAddressCity($request->request->get('delivery_address_city'))
            ->setDeliveryAddressCountry($request->request->get('delivery_address_country'))

            ->setMerchantCustomerId($request->request->get('merchant_customer_id'))
            ->setDebtorCompanyName($request->request->get('debtor_company_name'))
            ->setDebtorCompanyTaxId($request->request->get('debtor_company_tax_id'))
            ->setDebtorCompanyTaxNumber($request->request->get('debtor_company_tax_number'))
            ->setDebtorCompanyRegistrationCourt($request->request->get('debtor_company_registration_court'))
            ->setDebtorCompanyRegistrationNumber($request->request->get('debtor_company_registration_number'))
            ->setDebtorCompanyIndustrySector($request->request->get('debtor_company_industry_sector'))
            ->setDebtorCompanySubindustrySector($request->request->get('debtor_company_subindustry_sector'))
            ->setDebtorCompanyEmployeesNumber($request->request->get('debtor_company_employees_number'))
            ->setDebtorCompanyLegalForm($request->request->get('debtor_company_legal_form'))
            ->setDebtorCompanyEstablishedCustomer($request->request->get('debtor_company_established_customer'))

            ->setDebtorCompanyAddressAddition($request->request->get('debtor_company_address_addition'))
            ->setDebtorCompanyAddressHouseNumber($request->request->get('debtor_company_address_house_number'))
            ->setDebtorCompanyAddressStreet($request->request->get('debtor_company_address_street'))
            ->setDebtorCompanyAddressPostalCode($request->request->get('debtor_company_address_postal_code'))
            ->setDebtorCompanyAddressCity($request->request->get('debtor_company_address_city'))
            ->setDebtorCompanyAddressCountry($request->request->get('debtor_company_address_country'))

            ->setDebtorPersonGender($request->request->get('debtor_person_gender'))
            ->setDebtorPersonFirstName($request->request->get('debtor_person_first_name'))
            ->setDebtorPersonLastName($request->request->get('debtor_person_last_name'))
            ->setDebtorPersonPhoneNumber($request->request->get('debtor_person_phone_number'))
            ->setDebtorPersonEmail($request->request->get('debtor_person_email'))
        ;

        $this->useCase->execute($request);

        return new Response(null, Response::HTTP_CREATED);
    }
}
