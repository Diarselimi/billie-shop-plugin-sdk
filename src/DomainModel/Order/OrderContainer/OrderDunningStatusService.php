<?php

namespace App\DomainModel\Order\OrderContainer;

use App\DomainModel\Order\SalesforceInterface;
use App\Infrastructure\Salesforce\Exception\SalesforceException;

class OrderDunningStatusService
{
    private const STATUS_MAPPING = [
        null => 'not_started',
        'created' => 'active',
        'paused' => 'paused',
        'fully Paid' => 'inactive',
        'archived' => 'inactive',
        'unpaid' => 'active',
    ];

    private $salesforce;

    public function __construct(SalesforceInterface $salesforce)
    {
        $this->salesforce = $salesforce;
    }

    public function getStatus(string $orderUuid): ? string
    {
        try {
            $status = $this->salesforce->getOrderDunningStatus($orderUuid);
        } catch (SalesforceException $exception) {
            $status = null;
        }

        return self::STATUS_MAPPING[strtolower($status)] ?? null;
    }
}
