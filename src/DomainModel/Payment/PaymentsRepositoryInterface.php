<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Support\PaginatedCollection;

interface PaymentsRepositoryInterface
{
    public function get(string $merchantPaymentUuid, string $transactionUuid): array;

    public function search(SearchPaymentsDTO $paymentsDTO): PaginatedCollection;
}
