<?php

namespace App\DomainModel\OrderInvoice;

class LegacyOrderInvoiceEntity
{
    private $id;

    private $orderId;

    private $fileId;

    private $invoiceNumber;

    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): LegacyOrderInvoiceEntity
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

    public function setOrderId(int $orderId): LegacyOrderInvoiceEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getFileId(): int
    {
        return $this->fileId;
    }

    public function setFileId(int $fileId): LegacyOrderInvoiceEntity
    {
        $this->fileId = $fileId;

        return $this;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): LegacyOrderInvoiceEntity
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }
}
