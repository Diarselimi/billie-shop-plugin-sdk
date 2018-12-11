<?php

namespace App\DomainModel\OrderInvoice;

use DateTime;

class OrderInvoiceEntity
{
    private $id;

    private $orderId;

    private $fileId;

    private $invoiceNumber;

    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): OrderInvoiceEntity
    {
        $this->id = $id;

        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): OrderInvoiceEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getFileId(): int
    {
        return $this->fileId;
    }

    public function setFileId(int $fileId): OrderInvoiceEntity
    {
        $this->fileId = $fileId;

        return $this;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber($invoiceNumber): OrderInvoiceEntity
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
