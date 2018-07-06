<?php

namespace App\Application\UseCase\UpdateRiskCheckResult;

class UpdateRiskCheckResultRequest
{
    private $id;
    private $riskCheckId;

    public function __construct(int $id, int $riskCheckId)
    {
        $this->id = $id;
        $this->riskCheckId = $riskCheckId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRiskCheckId(): int
    {
        return $this->riskCheckId;
    }
}
