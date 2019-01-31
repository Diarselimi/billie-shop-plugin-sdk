<?php

namespace App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

class UpdateMerchantWithOrderDunningStepRequest
{
    private $orderUuid;

    private $step;

    public function __construct(string $orderUuid, string $step)
    {
        $this->orderUuid = $orderUuid;
        $this->step = $step;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    public function getStep(): string
    {
        return $this->step;
    }
}
