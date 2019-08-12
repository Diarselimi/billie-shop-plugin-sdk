<?php

namespace App\Http\RequestHandler;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\Request\AddressRequestFactory;
use App\Application\UseCase\CreateOrder\Request\AmountRequestFactory;
use App\Application\UseCase\CreateOrder\Request\DebtorPersonRequestFactory;
use App\Application\UseCase\CreateOrder\Request\DebtorRequestFactory;
use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

class CreateOrderRequestFactory
{
    private $addressRequestFactory;

    private $amountRequestFactory;

    private $debtorRequestFactory;

    private $debtorPersonRequestFactory;

    public function __construct(
        AmountRequestFactory $amountRequestFactory,
        DebtorRequestFactory $debtorRequestFactory,
        DebtorPersonRequestFactory $debtorPersonRequestFactory,
        AddressRequestFactory $addressRequestFactory
    ) {
        $this->addressRequestFactory = $addressRequestFactory;
        $this->amountRequestFactory = $amountRequestFactory;
        $this->debtorRequestFactory = $debtorRequestFactory;
        $this->debtorPersonRequestFactory = $debtorPersonRequestFactory;
    }

    public function createForCreateOrder(Request $request): CreateOrderRequest
    {
        $useCaseRequest = (new CreateOrderRequest())
            ->setAmount($this->amountRequestFactory->createFromArray($request->request->get('amount', [])))
            ->setCheckoutSessionId($request->attributes->get('checkout_session_id', null))
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setDuration($request->request->getInt('duration'))
            ->setComment($request->request->get('comment'))
            ->setExternalCode($request->request->get('order_id'))
            ->setDebtorCompany($this->debtorRequestFactory->createFromRequest($request->request->get('debtor_company', [])))
            ->setDebtorPerson($this->debtorPersonRequestFactory->createFromArray($request->request->get('debtor_person', [])))
        ;

        $useCaseRequest->setDeliveryAddress(
            $this->addressRequestFactory->createFromArray($request->request->get('delivery_address', []))
        );

        $useCaseRequest->setBillingAddress(
            $this->addressRequestFactory->createFromArray($request->request->get('billing_address', []))
        );

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
