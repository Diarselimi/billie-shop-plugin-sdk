<?php

namespace App\Http\RequestHandler;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDeliveryAddressRequest;
use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\DomainModel\Order\OrderRegistrationNumberConverter;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

class CreateOrderRequestFactory
{
    private $registrationNumberConverter;

    public function __construct(OrderRegistrationNumberConverter $registrationNumberConverter)
    {
        $this->registrationNumberConverter = $registrationNumberConverter;
    }

    public function createForCreateOrder(Request $request): CreateOrderRequest
    {
        $useCaseRequest = (new CreateOrderRequest())
            ->setAmount(
                (new CreateOrderAmountRequest())
                    ->setNet($request->request->get('amount')['net'] ?? null)
                    ->setGross($request->request->get('amount')['gross'] ?? null)
                    ->setTax($request->request->get('amount')['tax'] ?? null)
            )
            ->setCheckoutSessionId($request->attributes->get('checkout_session_id', null))
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setDuration($request->request->getInt('duration'))
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

        return $useCaseRequest;
    }

    public function createForAuthorizeCheckoutSession(
        Request $request,
        CheckoutSessionEntity $checkoutSessionEntity
    ): CreateOrderRequest {
        $request->request->set(
            'debtor_company',
            array_merge(
                $request->request->get('debtor_company'),
                ['merchant_customer_id' => $checkoutSessionEntity->getMerchantDebtorExternalId()]
            )
        );
        $request->attributes->set('checkout_session_id', $checkoutSessionEntity->getId());

        return $this->createForCreateOrder($request);
    }
}
