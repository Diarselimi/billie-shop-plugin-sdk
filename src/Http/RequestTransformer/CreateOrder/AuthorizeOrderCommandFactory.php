<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\AuthorizeOrder\AuthorizeOrder;
use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\Http\RequestTransformer\AmountRequestFactory;

class AuthorizeOrderCommandFactory
{
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

    public function create(
        array $request,
        CheckoutSession $checkoutSession,
        string $creationSource,
        ?string $expiration
    ): AuthorizeOrder {
        $command = (new AuthorizeOrder())
            ->setAmount($this->amountRequestFactory->createFromArray($request['amount'] ?? []))
            ->setCheckoutSessionId($checkoutSession->id())
            ->setCreationSource($creationSource)
            ->setWorkflowName($this->getWorkflowName($checkoutSession->merchantId()))
            ->setMerchantId($checkoutSession->merchantId())
            ->setDuration(empty($request['duration']) ? null : (int) $request['duration'])
            ->setComment($request['comment'] ?? null)
            ->setExternalCode($request['order_id'] ?? null)
            ->setDebtor($this->debtorRequestFactory->createForLegacyOrder($request['debtor_company'] ?? []))
            ->setDebtorPerson($this->debtorPersonRequestFactory->create($request['debtor_person'] ?? []))
            ->setExpiration($expiration);

        $command->setDeliveryAddress(
            $this->addressRequestFactory->createFromArray($request['delivery_address'] ?? null)
        );

        $command->setBillingAddress(
            $this->addressRequestFactory->createFromArray($request['billing_address'] ?? null)
        );

        $command->setLineItems(
            $this->lineItemsRequestFactory->create($request['line_items'] ?? [])
        );

        $command->getDebtor()->setMerchantCustomerId($checkoutSession->debtorExternalId());

        return $command;
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
