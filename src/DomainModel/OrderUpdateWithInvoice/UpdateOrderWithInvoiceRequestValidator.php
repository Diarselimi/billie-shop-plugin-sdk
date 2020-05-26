<?php

namespace App\DomainModel\OrderUpdateWithInvoice;

use App\Application\UseCase\UpdateOrderWithInvoice\UpdateOrderWithInvoiceRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderUpdate\UpdateOrderAmountValidator;

class UpdateOrderWithInvoiceRequestValidator
{
    private const ORDER_UPDATE_ALLOWED_STATES = [
        OrderStateManager::STATE_CREATED,
        OrderStateManager::STATE_WAITING,
    ];

    private $amountValidator;

    public function __construct(UpdateOrderAmountValidator $amountValidator)
    {
        $this->amountValidator = $amountValidator;
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
            ;
    }
}
