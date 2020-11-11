<?php

namespace App\DomainModel\OrderUpdate;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;

/**
 * Validates an UpdateOrderRequest together with the associated order data
 */
class UpdateOrderRequestValidator
{
    private const ORDER_UPDATE_ALLOWED_STATES = [
        OrderEntity::STATE_SHIPPED,
        OrderEntity::STATE_PAID_OUT,
        OrderEntity::STATE_LATE,
        OrderEntity::STATE_WAITING,
        OrderEntity::STATE_CREATED,
    ];

    private UpdateOrderAmountValidator $amountValidator;

    private UpdateOrderDurationValidator $durationValidator;

    private UpdateOrderExternalCodeValidator $externalCodeValidator;

    private UpdateOrderInvoiceNumberValidator $invoiceNumberValidator;

    private UpdateOrderInvoiceUrlValidator $invoiceUrlValidator;

    public function __construct(
        UpdateOrderAmountValidator $amountValidator,
        UpdateOrderDurationValidator $durationValidator,
        UpdateOrderExternalCodeValidator $externalCodeValidator,
        UpdateOrderInvoiceNumberValidator $invoiceNumberValidator,
        UpdateOrderInvoiceUrlValidator $invoiceUrlValidator
    ) {
        $this->amountValidator = $amountValidator;
        $this->durationValidator = $durationValidator;
        $this->externalCodeValidator = $externalCodeValidator;
        $this->invoiceNumberValidator = $invoiceNumberValidator;
        $this->invoiceUrlValidator = $invoiceUrlValidator;
    }

    public function getValidatedRequest(OrderContainer $orderContainer, UpdateOrderRequest $request): UpdateOrderRequest
    {
        return (new UpdateOrderRequest($request->getOrderId(), $request->getMerchantId()))
            ->setAmount($this->amountValidator->getValidatedValue(
                $orderContainer,
                $request->getAmount(),
                self::ORDER_UPDATE_ALLOWED_STATES
            ))
            ->setDuration($this->durationValidator->getValidatedValue($orderContainer, $request->getDuration()))
            ->setInvoiceNumber($this->invoiceNumberValidator->getValidatedValue($orderContainer, $request->getInvoiceNumber()))
            ->setInvoiceUrl($this->invoiceUrlValidator->getValidatedValue($orderContainer, $request->getInvoiceUrl()))
            ->setExternalCode($this->externalCodeValidator->getValidatedValue($orderContainer, $request->getExternalCode()))
        ;
    }
}
