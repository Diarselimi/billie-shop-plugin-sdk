<?php

namespace App\Infrastructure\Risky;

class RiskyResultDTO
{
    private $checkId;
    private $isPassed;

    public function __construct(bool $isPassed, ?int $checkId)
    {
        $this->checkId = $checkId;
        $this->isPassed = $isPassed;
    }

    public function getCheckId(): ?int
    {
        return $this->checkId;
    }

    public function isPassed(): bool
    {
        return $this->isPassed;
    }
}
