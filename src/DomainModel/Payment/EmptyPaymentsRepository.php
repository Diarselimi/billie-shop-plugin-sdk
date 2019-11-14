<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Support\PaginatedCollection;

class EmptyPaymentsRepository implements PaymentsRepositoryInterface
{
    public function get(string $merchantPaymentUuid, string $transactionUuid): array
    {
        return [];
    }

    public function search(SearchPaymentsDTO $paymentsDTO): PaginatedCollection
    {
        return new PaginatedCollection([], 0);
    }
}
