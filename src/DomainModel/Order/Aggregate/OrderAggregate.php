<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Aggregate;

use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;

class OrderAggregate
{
    private OrderEntity $order;

    private OrderFinancialDetailsEntity $financialDetails;

    private InvoiceCollection $invoices;

    public function __construct(
        OrderEntity $order,
        OrderFinancialDetailsEntity $financialDetails,
        InvoiceCollection $invoices
    ) {
        $this->order = $order;
        $this->financialDetails = $financialDetails;
        $this->invoices = $invoices;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getFinancialDetails(): OrderFinancialDetailsEntity
    {
        return $this->financialDetails;
    }

    public function getInvoices(): InvoiceCollection
    {
        return $this->invoices;
    }

    public function hasInvoices(): bool
    {
        return $this->invoices->count() > 0;
    }
}
