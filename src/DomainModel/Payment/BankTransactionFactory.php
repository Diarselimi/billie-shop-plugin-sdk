<?php

declare(strict_types=1);

namespace App\DomainModel\Payment;

use App\Support\AbstractFactory;
use Ozean12\Money\Money;

/**
 * @method BankTransaction[] createFromArrayMultiple(iterable $collection)
 */
class BankTransactionFactory extends AbstractFactory
{
    public function createFromArray(array $data): BankTransaction
    {
        return (new BankTransaction())
            ->setAmount(new Money($data['mapped_amount'] ?? $data['pending_amount']))
            ->setCreatedAt($this->getPaymentCreatedAt($data))
            ->setState($this->getPaymentState($data))
            ->setType($data['payment_type'])
            ->setTransactionUuid($data['transaction_uuid'] ?? null)
            ->setDebtorName($data['debtor_name'] ?? null);
    }

    private function getPaymentState(array $item): string
    {
        return ($item['mapped_at'] ?? null) ? BankTransaction::STATE_COMPLETE : BankTransaction::STATE_NEW;
    }

    private function getPaymentCreatedAt(array $item): \DateTime
    {
        return ($item['mapped_at'] ?? null) ? new \DateTime($item['mapped_at']) : new \DateTime($item['created_at']);
    }
}
