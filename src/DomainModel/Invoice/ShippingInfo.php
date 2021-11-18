<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

class ShippingInfo
{
    private ?string $returnShippingCompany;

    private ?string $returnTrackingNumber;

    private ?string $returnTrackingUrl;

    private ?string $shippingCompany;

    private ?string $shippingMethod;

    private ?string $trackingNumber;

    private ?string $trackingUrl;

    public function __construct(
        ?string $trackingUrl = null,
        ?string $trackingNumber = null,
        ?string $shippingMethod = null,
        ?string $shippingCompany = null,
        ?string $returnTrackingNumber = null,
        ?string $returnTrackingUrl = null,
        ?string $returnShippingCompany = null
    ) {
        $this->trackingUrl = $trackingUrl;
        $this->trackingNumber = $trackingNumber;
        $this->shippingMethod = $shippingMethod;
        $this->shippingCompany = $shippingCompany;
        $this->returnTrackingNumber = $returnTrackingNumber;
        $this->returnTrackingUrl = $returnTrackingUrl;
        $this->returnShippingCompany = $returnShippingCompany;
    }

    public function getReturnShippingCompany(): ?string
    {
        return $this->returnShippingCompany;
    }

    public function getReturnTrackingNumber(): ?string
    {
        return $this->returnTrackingNumber;
    }

    public function getReturnTrackingUrl(): ?string
    {
        return $this->returnTrackingUrl;
    }

    public function getShippingCompany(): ?string
    {
        return $this->shippingCompany;
    }

    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function getTrackingUrl(): ?string
    {
        return $this->trackingUrl;
    }
}
