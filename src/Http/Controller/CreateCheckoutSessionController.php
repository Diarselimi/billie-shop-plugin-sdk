<?php

namespace App\Http\Controller;

use App\Application\UseCase\CheckoutSession\CreateCheckoutSessionRequest;
use App\Application\UseCase\CheckoutSession\CreateCheckoutSessionUseCase;
use App\DomainModel\ArrayableInterface;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

class CreateCheckoutSessionController
{
    private $useCase;

    public function __construct(CreateCheckoutSessionUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): ArrayableInterface
    {
        $createCheckoutSession = (new CreateCheckoutSessionRequest())
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setMerchantDebtorExternalId($request->request->get('merchant_customer_id'));

        return $this->useCase->execute($createCheckoutSession);
    }
}
