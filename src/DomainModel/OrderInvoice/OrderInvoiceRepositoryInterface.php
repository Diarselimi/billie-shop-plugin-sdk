<?php

namespace App\DomainModel\OrderInvoice;

interface OrderInvoiceRepositoryInterface
{
    public function insert(OrderInvoiceEntity $orderInvoiceEntity): OrderInvoiceEntity;
}
