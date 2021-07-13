<?php

namespace App\DomainModel\Salesforce;

class ClaimState
{
    private ?string $dunningState;

    private ?string $dunningStage;

    private ?string $collectionState;

    private ?string $collectionStage;

    public function __construct(?string $dunningState, ?string $dunningStage, ?string $collectionState, ?string $collectionStage)
    {
        $this->dunningState = $dunningState;
        $this->dunningStage = $dunningStage;
        $this->collectionState = $collectionState;
        $this->collectionStage = $collectionStage;
    }

    public function getDunningState(): ?string
    {
        return $this->dunningState;
    }

    public function getDunningStage(): ?string
    {
        return $this->dunningStage;
    }

    public function getCollectionState(): ?string
    {
        return $this->collectionState;
    }

    public function getCollectionStage(): ?string
    {
        return $this->collectionStage;
    }
}
