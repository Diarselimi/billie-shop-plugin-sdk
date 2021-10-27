<?php

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderEntity;
use Billie\PdoBundle\DomainModel\CreatedAtEntityTrait;

class OrderInvoiceEntity
{
    use CreatedAtEntityTrait;

    private int $id;

    private int $orderId;

    private string $invoiceUuid;

    private ?OrderEntity $order;

    private ?Invoice $invoice;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function setInvoiceUuid(string $invoiceUuid): self
    {
        $this->invoiceUuid = $invoiceUuid;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): OrderInvoiceEntity
    {
        $this->order = $order;

        return $this;
    }
}
