<?php

declare(strict_types=1);

namespace App\DomainModel\Salesforce;

class PauseDunningRequestBuilder
{
    private int $numberOfDays;

    private string $orderUuid;

    private ?string $invoiceUuid;

    public function __construct(string $orderUuid, ?string $invoiceUuid, int $numberOfDays)
    {
        $this->numberOfDays = $numberOfDays;
        $this->orderUuid = $orderUuid;
        $this->invoiceUuid = $invoiceUuid;
    }

    public function getRequests(): \Generator
    {
        if ($this->invoiceUuid !== null) {
            yield [
                'referenceUuid' => $this->invoiceUuid,
                'numberOfDays' => $this->numberOfDays,
            ];
        }

        yield [
            'referenceUuid' => $this->orderUuid,
            'numberOfDays' => $this->numberOfDays,
        ];
    }
}
