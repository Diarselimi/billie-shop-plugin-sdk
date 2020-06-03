<?php

declare(strict_types=1);

namespace App\DomainModel\OrderUpdateWithInvoice;

use App\Application\UseCase\UpdateOrderWithInvoice\UpdateOrderWithInvoiceRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderUpdate\UpdateOrderAmountValidator;
use App\DomainModel\OrderUpdate\UpdateOrderInvoiceNumberValidator;

class UpdateOrderWithInvoiceRequestValidator
{
    private const ORDER_UPDATE_ALLOWED_STATES = [
        OrderStateManager::STATE_CREATED,
        OrderStateManager::STATE_WAITING,
        OrderStateManager::STATE_SHIPPED,
        OrderStateManager::STATE_PAID_OUT,
        OrderStateManager::STATE_LATE,
    ];

    private $amountValidator;

    private $invoiceNumberValidator;

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
