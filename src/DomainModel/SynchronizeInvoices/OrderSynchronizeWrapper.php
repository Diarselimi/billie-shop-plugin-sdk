<?php

namespace App\DomainModel\SynchronizeInvoices;

class OrderSynchronizeWrapper
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(string $key)
    {
        return $this->data[$key];
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function getId(): int
    {
        return $this->data['id'];
    }

    public function hasInvoiceInButler(): bool
    {
        return $this->data['invoice_uuid'] !== null;
    }
}
