<?php

namespace App\DomainModel\Salesforce;

interface SalesforceInterface
{
    public function pauseDunning(PauseDunningRequestBuilder $requestBuilder): void;

    public function getOrderClaimState(string $uuid): ClaimState;
}
