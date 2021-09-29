<?php

declare(strict_types=1);

namespace App\DomainEvent\AnalyticsEvent;

use App\Application\Tracking\TrackingEvent;
use App\DomainModel\Address\Address;
use App\DomainModel\DebtorExternalData\DebtorExternalData;

class TrackingItsNotUsEvent implements TrackingEvent
{
    private const EVENT_NAME = "BC_re-identification_completed";

    private string $sessionUuid;

    private DebtorExternalData $company;

    private Address $address;

    private int $merchantId;

    public function __construct(int $merchantId, string $sessionUuid, DebtorExternalData $company, Address $address)
    {
        $this->sessionUuid = $sessionUuid;
        $this->company = $company;
        $this->address = $address;
        $this->merchantId = $merchantId;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getPayload(): array
    {
        return [
            'session_uuid' => $this->sessionUuid,
            'debtor_company_data' => [
                'name' => $this->company->getCompanyName(),
                'merchant_customer_id' => $this->company->getMerchantCustomerId(),
                'address' => [
                    'street' => $this->address->getStreet(),
                    'house_number' => $this->address->getHouseNumber(),
                    'city' => $this->address->getCity(),
                    'country' => $this->address->getCountry(),
                ],
            ],
        ];
    }
}
