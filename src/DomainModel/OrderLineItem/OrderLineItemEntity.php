<?php

namespace App\DomainModel\OrderLineItem;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class OrderLineItemEntity extends AbstractTimestampableEntity
{
    private $orderId;

    private $externalId;

    private $title;

    private $description;

    private $quantity;

    private $category;

    private $brand;

    private $gtin;

    private $mpn;

    private $amountGross;

    private $amountNet;

    private $amountTax;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): OrderLineItemEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): OrderLineItemEntity
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): OrderLineItemEntity
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): OrderLineItemEntity
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): OrderLineItemEntity
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): OrderLineItemEntity
    {
        $this->category = $category;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): OrderLineItemEntity
    {
        $this->brand = $brand;

        return $this;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function setGtin(?string $gtin): OrderLineItemEntity
    {
        $this->gtin = $gtin;

        return $this;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(?string $mpn): OrderLineItemEntity
    {
        $this->mpn = $mpn;

        return $this;
    }

    public function getAmountGross(): float
    {
        return $this->amountGross;
    }

    public function setAmountGross(float $amountGross): OrderLineItemEntity
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    public function getAmountNet(): float
    {
        return $this->amountNet;
    }

    public function setAmountNet(float $amountNet): OrderLineItemEntity
    {
        $this->amountNet = $amountNet;

        return $this;
    }

    public function getAmountTax(): float
    {
        return $this->amountTax;
    }

    public function setAmountTax(float $amountTax): OrderLineItemEntity
    {
        $this->amountTax = $amountTax;

        return $this;
    }
}
