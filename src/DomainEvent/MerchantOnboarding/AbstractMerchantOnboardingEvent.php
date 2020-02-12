<?php

declare(strict_types=1);

namespace App\DomainEvent\MerchantOnboarding;

use App\DomainEvent\AbstractMerchantEvent;

abstract class AbstractMerchantOnboardingEvent extends AbstractMerchantEvent
{
    private $transitionName;

    public function __construct(int $merchantId, string $transitionName = null)
    {
        parent::__construct($merchantId);

        $this->transitionName = $transitionName;
    }

    public function getTransitionName(): ?string
    {
        return $this->transitionName;
    }

    abstract public function getTrackingEventName(): string;
}
