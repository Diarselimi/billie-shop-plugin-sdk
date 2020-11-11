<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\DomainModel\Order\OrderEntity;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\AmountRequestFactory;
use Symfony\Component\HttpFoundation\Request;

class CreateOrderRequestFactory
{
    private const DEFAULT_WORKFLOW = OrderEntity::WORKFLOW_NAME_V1;

    private AddressRequestFactory $addressRequestFactory;

    private DebtorRequestFactory $debtorRequestFactory;

    private DebtorPersonRequestFactory $debtorPersonRequestFactory;

    private OrderLineItemsRequestFactory $lineItemsRequestFactory;

    private AmountRequestFactory $amountRequestFactory;

    public function __construct(
        DebtorRequestFactory $debtorRequestFactory,
        DebtorPersonRequestFactory $debtorPersonRequestFactory,
        AddressRequestFactory $addressRequestFactory,
        OrderLineItemsRequestFactory $lineItemsRequestFactory,
        AmountRequestFactory $amountRequestFactory
    ) {
        $this->addressRequestFactory = $addressRequestFactory;
        $this->debtorRequestFactory = $debtorRequestFactory;
        $this->debtorPersonRequestFactory = $debtorPersonRequestFactory;
        $this->lineItemsRequestFactory = $lineItemsRequestFactory;
        $this->amountRequestFactory = $amountRequestFactory;
    }

    public function createForCreateOrder(Request $request): CreateOrderRequest
    {
        $useCaseRequest = (new CreateOrderRequest())
            ->setAmount($this->amountRequestFactory->create($request))
            ->setCheckoutSessionId($request->attributes->get('checkout_session_id', null))
            ->setCreationSource($request->attributes->get(
                HttpConstantsInterface::REQUEST_ATTRIBUTE_CREATION_SOURCE,
                OrderEntity::CREATION_SOURCE_API
            ))
            ->setWorkflowName($request->attributes->get('workflow_name', self::DEFAULT_WORKFLOW))
            ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
            ->setDuration($request->request->getInt('duration'))
            ->setComment($request->request->get('comment'))
            ->setExternalCode($request->request->get('order_id'))
            ->setDebtorCompany($this->debtorRequestFactory->create($request))
            ->setDebtorPerson($this->debtorPersonRequestFactory->create($request))
        ;

        $useCaseRequest->setDeliveryAddress(
            $this->addressRequestFactory->create($request, 'delivery_address')
        );

        $useCaseRequest->setBillingAddress(
            $this->addressRequestFactory->create($request, 'billing_address')
        );

        $useCaseRequest->setLineItems(
            $this->lineItemsRequestFactory->create($request)
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
