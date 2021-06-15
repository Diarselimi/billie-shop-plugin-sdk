<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\LegacyCreateOrderRequest;
use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
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

    private MerchantSettingsRepositoryInterface $merchantSettingsRepository;

    public function __construct(
        DebtorRequestFactory $debtorRequestFactory,
        DebtorPersonRequestFactory $debtorPersonRequestFactory,
        AddressRequestFactory $addressRequestFactory,
        OrderLineItemsRequestFactory $lineItemsRequestFactory,
        AmountRequestFactory $amountRequestFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $this->addressRequestFactory = $addressRequestFactory;
        $this->debtorRequestFactory = $debtorRequestFactory;
        $this->debtorPersonRequestFactory = $debtorPersonRequestFactory;
        $this->lineItemsRequestFactory = $lineItemsRequestFactory;
        $this->amountRequestFactory = $amountRequestFactory;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
    }

    public function createForLegacyCreateOrder(Request $request): LegacyCreateOrderRequest
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
        $creationSource = $request->attributes->get(
            HttpConstantsInterface::REQUEST_ATTRIBUTE_CREATION_SOURCE,
            OrderEntity::CREATION_SOURCE_API
        );

        if ($creationSource === OrderEntity::CREATION_SOURCE_DASHBOARD) {
            $request->attributes->set('workflow_name', $this->getWorkflowName($merchantId));
        }

        $useCaseRequest = (new LegacyCreateOrderRequest())
            ->setAmount($this->amountRequestFactory->create($request))
            ->setCheckoutSessionId($request->attributes->get('checkout_session_id', null))
            ->setCreationSource($creationSource)
            ->setWorkflowName($request->attributes->get('workflow_name', self::DEFAULT_WORKFLOW))
            ->setMerchantId($merchantId)
            ->setDuration($request->request->getInt('duration'))
            ->setComment($request->request->get('comment'))
            ->setExternalCode($request->request->get('order_id'))
            ->setDebtorCompany($this->debtorRequestFactory->createForLegacyOrder($request))
            ->setDebtorPerson($this->debtorPersonRequestFactory->create($request));

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
    ): LegacyCreateOrderRequest {
        $request->request->set(
            'debtor_company',
            array_merge(
                $request->request->get('debtor_company'),
                ['merchant_customer_id' => $checkoutSessionEntity->getMerchantDebtorExternalId()]
            )
        );

        $request->attributes->set('checkout_session_id', $checkoutSessionEntity->getId());
        $request->attributes->set('workflow_name', $this->getWorkflowName($checkoutSessionEntity->getMerchantId()));

        return $this->createForLegacyCreateOrder($request);
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
            ->setExternalCode($request->request->get('external_code'))
            ->setDebtorCompany($this->debtorRequestFactory->create($request))
            ->setDebtorPerson($this->debtorPersonRequestFactory->create($request));

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

    /**
     * @TODO: a temporary way to allow v2 order creation from checkout widget
     */
    private function getWorkflowName(int $merchantId): string
    {
        return $this->merchantSettingsRepository->getOneByMerchant($merchantId)->getDebtorForgivenessThreshold() === 42.
            ? OrderEntity::WORKFLOW_NAME_V2
            : OrderEntity::WORKFLOW_NAME_V1;
    }
}
