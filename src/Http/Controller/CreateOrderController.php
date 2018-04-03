<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
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
            ->setAmount($request->request->get('amount'))
            ->setDuration($request->request->get('duration'))
            ->setComment($request->request->get('comment'))
            ->setExternalCode($request->request->get('external_code'))

            ->setDeliveryAddressHouseNumber($request->request->get('delivery_address_house_number'))
            ->setDeliveryAddressStreet($request->request->get('delivery_address_street'))
            ->setDeliveryAddressPostalCode($request->request->get('delivery_address_postal_code'))
            ->setDeliveryAddressCity($request->request->get('delivery_address_city'))
            ->setDeliveryAddressCountry($request->request->get('delivery_address_country'))

            ->setDebtorCompanyAddressAddition($request->request->get('debtor_company_address_addition'))
            ->setDeliveryAddressHouseNumber($request->request->get('debtor_company_address_house_number'))
            ->setDebtorCompanyAddressStreet($request->request->get('debtor_company_address_street'))
            ->setDebtorCompanyAddressPostalCode($request->request->get('debtor_company_address_postal_code'))
            ->setDebtorCompanyAddressCity($request->request->get('debtor_company_address_city'))
            ->setDebtorCompanyAddressCountry($request->request->get('debtor_company_address_country'))
        ;

        $this->useCase->execute($request);

        return new Response(null, Response::HTTP_CREATED);
    }
}
