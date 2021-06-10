<?php

namespace App\Infrastructure\Volt;

use App\DomainModel\Fee\Fee;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;

class VoltResponseFactory
{
    public function createFeeFromResponse(array $data): Fee
    {
        return new Fee(
            new Percent($data['fee_rate'], 2),
            new Money($data['fee_amount'], 2),
            new Money($data['fee_net_amount'], 2),
            new Money($data['fee_vat_amount'], 2)
        );
    }
}
