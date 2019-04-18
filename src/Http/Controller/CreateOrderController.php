<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDeliveryAddressRequest;
use App\DomainModel\Order\OrderRegistrationNumberConverter;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateOrderController
{
    private $createOrderUseCase;

    private $registrationNumberConverter;

    public function __construct(
        CreateOrderUseCase $createOrderUseCase,
        OrderRegistrationNumberConverter $registrationNumberConverter
    ) {
        $this->createOrderUseCase = $createOrderUseCase;
        $this->registrationNumberConverter = $registrationNumberConverter;
    }

    public function execute(Request $request): JsonResponse
    {
        $useCaseRequest = (new CreateOrderRequest())
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setAmount(
                (new CreateOrderAmountRequest())
                    ->setNet($request->request->get('amount')['net'] ?? null)
                    ->setGross($request->request->get('amount')['gross'] ?? null)
                    ->setTax($request->request->get('amount')['tax'] ?? null)
            )
            ->setDuration($request->request->get('duration'))
            ->setComment($request->request->get('comment'))
            ->setExternalCode($request->request->get('order_id'))
            ->setDebtorCompany(
                (new CreateOrderDebtorCompanyRequest())
                    ->setMerchantCustomerId($request->request->get('debtor_company')['merchant_customer_id'] ?? null)
                    ->setName($request->request->get('debtor_company')['name'] ?? null)
                    ->setTaxId($request->request->get('debtor_company')['tax_id'] ?? null)
                    ->setTaxNumber($request->request->get('debtor_company')['tax_number'] ?? null)
                    ->setRegistrationCourt($request->request->get('debtor_company')['registration_court'] ?? null)
                    ->setIndustrySector($request->request->get('debtor_company')['industry_sector'] ?? null)
                    ->setSubindustrySector($request->request->get('debtor_company')['subindustry_sector'] ?? null)
                    ->setEmployeesNumber($request->request->get('debtor_company')['employees_number'] ?? null)
                    ->setLegalForm($request->request->get('debtor_company')['legal_form'] ?? null)
                    ->setEstablishedCustomer((bool) $request->request->get('debtor_company')['established_customer'] ?? null)
                    ->setAddressAddition($request->request->get('debtor_company')['address_addition'] ?? null)
                    ->setAddressHouseNumber($request->request->get('debtor_company')['address_house_number'] ?? null)
                    ->setAddressStreet($request->request->get('debtor_company')['address_street'] ?? null)
                    ->setAddressPostalCode($request->request->get('debtor_company')['address_postal_code'] ?? null)
                    ->setAddressCity($request->request->get('debtor_company')['address_city'] ?? null)
                    ->setAddressCountry($request->request->get('debtor_company')['address_country'] ?? null)
            )
            ->setDebtorPerson(
                (new CreateOrderDebtorPersonRequest())
                    ->setGender($request->request->get('debtor_person')['salutation'] ?? null)
                    ->setFirstName($request->request->get('debtor_person')['first_name'] ?? null)
                    ->setLastName($request->request->get('debtor_person')['last_name'] ?? null)
                    ->setPhoneNumber($request->request->get('debtor_person')['phone_number'] ?? null)
                    ->setEmail($request->request->get('debtor_person')['email'] ?? null)
            )
        ;

        if (
            isset($request->request->get('debtor_company')['registration_number'])
            && !is_null($request->request->get('debtor_company')['registration_number'])
        ) {
            $useCaseRequest->getDebtorCompany()->setRegistrationNumber(
                $this->registrationNumberConverter->convert(
                    $request->request->get('debtor_company')['registration_number'],
                    $request->request->get('debtor_company')['registration_court'] ?? ''
                )
            );
        }

        if ($request->request->get('delivery_address') && !empty($request->request->get('delivery_address'))) {
            $useCaseRequest->setDeliveryAddress(
                (new CreateOrderDeliveryAddressRequest())
                    ->setHouseNumber($request->request->get('delivery_address')['house_number'] ?? null)
                    ->setStreet($request->request->get('delivery_address')['street'] ?? null)
                    ->setPostalCode($request->request->get('delivery_address')['postal_code'] ?? null)
                    ->setCity($request->request->get('delivery_address')['city'] ?? null)
                    ->setCountry($request->request->get('delivery_address')['country'] ?? null)
            );
        } else {
            $useCaseRequest->setDeliveryAddress(
                (new CreateOrderDeliveryAddressRequest())
                    ->setHouseNumber($request->request->get('debtor_company')['address_house_number'] ?? null)
                    ->setStreet($request->request->get('debtor_company')['address_street'] ?? null)
                    ->setPostalCode($request->request->get('debtor_company')['address_postal_code'] ?? null)
                    ->setCity($request->request->get('debtor_company')['address_city'] ?? null)
                    ->setCountry($request->request->get('debtor_company')['address_country'] ?? null)
            );
        }

        $response = $this->createOrderUseCase->execute($useCaseRequest);

        return new JsonResponse($response->toArray(), JsonResponse::HTTP_CREATED);
    }
}
