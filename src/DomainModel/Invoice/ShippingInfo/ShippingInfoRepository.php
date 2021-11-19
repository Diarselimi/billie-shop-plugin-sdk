<?php

namespace App\DomainModel\Invoice\ShippingInfo;

use App\DomainModel\Invoice\ShippingInfo;

interface ShippingInfoRepository
{
    public function save(ShippingInfo $shippingInfo): void;
}
