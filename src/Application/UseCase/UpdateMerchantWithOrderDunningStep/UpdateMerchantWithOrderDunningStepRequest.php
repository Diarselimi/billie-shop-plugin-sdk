<?php

namespace App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

class UpdateMerchantWithOrderDunningStepRequest
{
    private string $orderUuid;

    private ?string $invoiceUuid;

    private string $step;

    public function __construct(string $orderUuid, ?string $invoiceUuid, string $step)
    {
        $this->orderUuid = $orderUuid;
        $this->invoiceUuid = $invoiceUuid;
        $this->step = $step;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    public function getInvoiceUuid(): ?string
    {
        return $this->invoiceUuid;
    }

    public function getStep(): string
    {
        return $this->step;
    }
}
