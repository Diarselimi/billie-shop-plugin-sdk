<?php

namespace App\DomainModel\VatRate;

use Ozean12\Money\Percent;

interface VatRateRepositoryInterface
{
    public function getForDateTime(\DateTime $currentDateTime): Percent;
}
