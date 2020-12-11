<?php

namespace App\DomainModel\OrderInvoice;

interface LegacyOrderInvoiceRepositoryInterface
{
    public function insert(LegacyOrderInvoiceEntity $orderInvoiceEntity): LegacyOrderInvoiceEntity;
}
