<?php

declare(strict_types=1);

namespace App\DomainModel\OrderUpdateWithInvoice;

use App\Application\UseCase\UpdateOrderWithInvoice\UpdateOrderWithInvoiceRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderUpdate\UpdateOrderAmountValidator;
use App\DomainModel\OrderUpdate\UpdateOrderInvoiceNumberValidator;

class UpdateOrderWithInvoiceRequestValidator
{
    private const ORDER_UPDATE_ALLOWED_STATES = [
        OrderEntity::STATE_CREATED,
        OrderEntity::STATE_WAITING,
        OrderEntity::STATE_SHIPPED,
        OrderEntity::STATE_PAID_OUT,
        OrderEntity::STATE_LATE,
    ];

    private UpdateOrderAmountValidator $amountValidator;

    private UpdateOrderInvoiceNumberValidator $invoiceNumberValidator;

    public function __construct(
        UpdateOrderAmountValidator $amountValidator,
        UpdateOrderInvoiceNumberValidator $invoiceNumberValidator
    ) {
        $this->amountValidator = $amountValidator;
        $this->invoiceNumberValidator = $invoiceNumberValidator;
    }

    public function getValidatedRequest(
        OrderContainer $orderContainer,
        UpdateOrderWithInvoiceRequest $request
    ): UpdateOrderWithInvoiceRequest {
        return (new UpdateOrderWithInvoiceRequest($request->getOrderId(), $request->getMerchantId()))
            ->setAmount($this->amountValidator->getValidatedValue(
                $orderContainer,
                $request->getAmount(),
                self::ORDER_UPDATE_ALLOWED_STATES
            ))
            ->setInvoiceNumber($this->invoiceNumberValidator->getValidatedValue(
                $orderContainer,
                $request->getInvoiceNumber()
            ));
    }
}
