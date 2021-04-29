<?php

namespace App\DomainModel\Fee;

use App\DomainModel\Invoice\Invoice;

interface FeeService
{
    public function getFee(Invoice $invoice): Fee;
}
