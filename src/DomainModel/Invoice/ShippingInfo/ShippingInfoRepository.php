<?php

namespace App\DomainModel\Invoice\ShippingInfo;

use App\DomainModel\Invoice\Invoice;

interface ShippingInfoRepository
{
    public function save(Invoice $invoice): void;
}
