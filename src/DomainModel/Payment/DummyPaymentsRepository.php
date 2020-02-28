<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Support\PaginatedCollection;

/**
 * TESTING purpose only!
 * Since we don't have easy way to mock data from graphql endpoints, we are doing it through dummy class
 */
class DummyPaymentsRepository implements PaymentsRepositoryInterface
{
    public function getPaymentDetails(string $merchantPaymentUuid, string $transactionUuid): array
    {
        return [];
    }

    public function searchMerchantPayments(SearchPaymentsDTO $paymentsDTO): PaginatedCollection
    {
        return new PaginatedCollection([], 0);
    }

    public function getOrderPayments(string $orderPaymentUuid): PaginatedCollection
    {
        return new PaginatedCollection(
            [
                [
                    "created_at" => "2018-06-28T17:10:05Z",
                    "mapped_at" => "2018-07-11T11:06:35Z",
                    "mapped_amount" => 67.12,
                    "pending_amount" => 67.12,
                    "transaction_uuid" => "fc23cb4e-77c3-11e9-a2c4-02c6850949d6",
                    "payment_type" => "invoice_payback",
                ],
                [
                    "created_at" => "2018-06-28T17:10:05Z",
                    "mapped_at" => null,
                    "mapped_amount" => null,
                    "pending_amount" => 67.12,
                    "transaction_uuid" => null,
                    "payment_type" => "invoice_payback",
                ],
            ],
            1
        );
    }
}
