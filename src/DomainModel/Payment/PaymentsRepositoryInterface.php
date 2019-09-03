<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;

interface PaymentsRepositoryInterface
{
    public function search(SearchPaymentsDTO $paymentsDTO): array;
}
