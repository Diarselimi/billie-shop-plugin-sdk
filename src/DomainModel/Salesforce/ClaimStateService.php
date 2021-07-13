<?php

namespace App\DomainModel\Salesforce;

use App\Infrastructure\Salesforce\Exception\SalesforceException;

class ClaimStateService
{
    private const DUNNING_STATE_MAPPING = [
        null => 'not_started',
        'created' => 'active',
        'paused' => 'paused',
        'fully_paid' => 'inactive',
        'archived' => 'inactive',
        'unpaid' => 'active',
    ];

    private SalesforceInterface $salesforce;

    public function __construct(SalesforceInterface $salesforce)
    {
        $this->salesforce = $salesforce;
    }

    public function getClaimDunningState(string $orderOrInvoiceUuid): ?string
    {
        try {
            $state = $this->salesforce->getOrderClaimState($orderOrInvoiceUuid)->getDunningState();
        } catch (SalesforceException $exception) {
            $state = null;
        }

        return self::DUNNING_STATE_MAPPING[str_replace(' ', '_', strtolower($state))] ?? null;
    }

    public function isInCollection(string $orderOrInvoiceUuid): bool
    {
        return $this->salesforce->getOrderClaimState($orderOrInvoiceUuid)->getCollectionStage() !== null;
    }
}
