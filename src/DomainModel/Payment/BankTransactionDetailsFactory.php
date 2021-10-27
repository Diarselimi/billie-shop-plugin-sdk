<?php

declare(strict_types=1);

namespace App\DomainModel\Payment;

use Ozean12\Money\Money;
use Ramsey\Uuid\Uuid;

class BankTransactionDetailsFactory
{
    public function fromArray(array $data): BankTransactionDetails
    {
        $data = $this->preprocessData($data);

        $orders = new BankTransactionDetailsOrderCollection(
            array_map(
                static function (array $orderData): BankTransactionDetailsOrder {
                    return new BankTransactionDetailsOrder(
                        Uuid::fromString($orderData['uuid']),
                        new Money($orderData['amount']),
                        new Money($orderData['mapped_amount']),
                        new Money($orderData['outstanding_amount']),
                        $orderData['external_id'],
                        $orderData['invoice_number']
                    );
                },
                $data['orders']
            )
        );

        return new BankTransactionDetails(
            Uuid::fromString($data['uuid']),
            new Money($data['amount']),
            new Money($data['overpaid_amount']),
            (bool) $data['is_allocated'],
            $orders,
            $data['merchant_debtor_uuid'] ? Uuid::fromString($data['merchant_debtor_uuid']) : null,
            $data['transaction_counterparty_iban'],
            $data['transaction_counterparty_name'],
            $data['transaction_date'] ? new \DateTimeImmutable($data['transaction_date']) : null,
            $data['transaction_reference'],
        );
    }

    private function preprocessData(array $data): array
    {
        $overpayment = 0;
        $orders = [];

        if (!empty($data['orders'])) {
            $orders = array_filter(
                $data['orders'],
                static function (array $order) use (&$overpayment) {
                    if ($order['uuid'] !== null) {
                        return $order;
                    }

                    $overpayment += $order['mapped_amount'];

                    return null;
                }
            );
        }
        $data['orders'] = array_values($orders);
        $data['overpaid_amount'] = $overpayment;

        return $data;
    }
}
