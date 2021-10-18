<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\LegacyCreateOrderRequest;
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
            ->setDebtor($this->debtorRequestFactory->createForLegacyOrder($request->request->get('debtor_company')))
            ->setDebtorPerson($this->debtorPersonRequestFactory->create($request->request->get('debtor_person', [])));

        $useCaseRequest->setDeliveryAddress(
            $this->addressRequestFactory->create($request, 'delivery_address')
        );

        $useCaseRequest->setBillingAddress(
            $this->addressRequestFactory->create($request, 'billing_address')
        );

        $useCaseRequest->setLineItems(
            $this->lineItemsRequestFactory->create($request->request->get('line_items', []))
        );

        return $useCaseRequest;
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
            ->setDebtor($this->debtorRequestFactory->create($request))
            ->setDebtorPerson($this->debtorPersonRequestFactory->create($request->request->get('debtor_person', [])));

        $useCaseRequest->setDeliveryAddress(
            $this->addressRequestFactory->create($request, 'delivery_address')
        );

        if (!empty($request->request->get('debtor')['billing_address'])) {
            $useCaseRequest->setBillingAddress(
                $this->addressRequestFactory->createFromArray($request->request->get('debtor')['billing_address'])
            );
        }

        $useCaseRequest->setLineItems(
            $this->lineItemsRequestFactory->create($request->request->get('line_items', []))
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
