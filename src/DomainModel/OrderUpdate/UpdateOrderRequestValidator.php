<?php

namespace App\DomainModel\OrderUpdate;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;

/**
 * Validates an UpdateOrderRequest together with the associated order data
 */
class UpdateOrderRequestValidator
{
    private $amountValidator;

    private $durationValidator;

    private $externalCodeValidator;

    private $invoiceNumberValidator;

    private $invoiceUrlValidator;

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
            ->setAmount($this->amountValidator->getValidatedValue($orderContainer, $request->getAmount()))
            ->setDuration($this->durationValidator->getValidatedValue($orderContainer, $request->getDuration()))
            ->setInvoiceNumber($this->invoiceNumberValidator->getValidatedValue($orderContainer, $request->getInvoiceNumber()))
            ->setInvoiceUrl($this->invoiceUrlValidator->getValidatedValue($orderContainer, $request->getInvoiceUrl()))
            ->setExternalCode($this->externalCodeValidator->getValidatedValue($orderContainer, $request->getExternalCode()))
        ;
    }
}
