<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\Payment;

use App\DomainModel\Payment\BankTransaction;
use App\DomainModel\Payment\BankTransactionFactory;
use App\Tests\Unit\UnitTestCase;
use DateTime;
use Ozean12\Money\Money;

/**
 * @see BankTransactionFactory
 */
class BankTransactionFactoryTest extends UnitTestCase
{
    /**
     * @test
     *
     * @param array           $data
     * @param BankTransaction $expected
     * @dataProvider createFromArrayShouldMatchDataProvider
     */
    public function createFromArrayShouldMatch(array $data, BankTransaction $expected): void
    {
        $factory = new BankTransactionFactory();
        $actual = $factory->createFromArray($data);

        self::assertMoneyEquals($expected->getAmount(), $actual->getAmount());
        self::assertDateEquals($expected->getCreatedAt(), $actual->getCreatedAt());
        self::assertEquals($expected->getState(), $actual->getState());
        self::assertEquals($expected->getType(), $actual->getType());
        self::assertEquals($expected->getTransactionUuid(), $actual->getTransactionUuid());
        self::assertEquals($expected->getDebtorName(), $actual->getDebtorName());
    }

    public function createFromArrayShouldMatchDataProvider(): array
    {
        $mappedAmount = 1.23;
        $pendingAmount = 4.56;
        $transactionUuid = '29a4766d-65b1-4d2a-9550-78a701fdb9fa';
        $debtorName = 'Foobar GmbH';
        $mappedAt = '2020-12-31 00:00:00';
        $createdAt = '2020-12-22 00:00:00';

        return [
            'merchant payment with all fields' => [
                [
                    'mapped_amount' => $mappedAmount,
                    'pending_amount' => $pendingAmount,
                    'mapped_at' => $mappedAt,
                    'created_at' => $createdAt,
                    'payment_type' => BankTransaction::TYPE_MERCHANT_PAYMENT,
                    'transaction_uuid' => $transactionUuid,
                    'debtor_name' => $debtorName,
                ],
                (new BankTransaction())
                    ->setAmount(new Money($mappedAmount))
                    ->setCreatedAt(new DateTime($mappedAt))
                    ->setState(BankTransaction::STATE_COMPLETE)
                    ->setType(BankTransaction::TYPE_MERCHANT_PAYMENT)
                    ->setTransactionUuid($transactionUuid)
                    ->setDebtorName($debtorName),
            ],
            'merchant payment without: mapped_at, pending_amount' => [
                [
                    'mapped_amount' => $mappedAmount,
                    'created_at' => $createdAt,
                    'payment_type' => BankTransaction::TYPE_MERCHANT_PAYMENT,
                    'transaction_uuid' => $transactionUuid,
                    'debtor_name' => $debtorName,
                ],
                (new BankTransaction())
                    ->setAmount(new Money($mappedAmount))
                    ->setCreatedAt(new DateTime($createdAt))
                    ->setState(BankTransaction::STATE_NEW)
                    ->setType(BankTransaction::TYPE_MERCHANT_PAYMENT)
                    ->setTransactionUuid($transactionUuid)
                    ->setDebtorName($debtorName),
            ],
            'merchant payment without: created_at, pending_amount' => [
                [
                    'mapped_amount' => $mappedAmount,
                    'mapped_at' => $mappedAt,
                    'payment_type' => BankTransaction::TYPE_MERCHANT_PAYMENT,
                    'transaction_uuid' => $transactionUuid,
                    'debtor_name' => $debtorName,
                ],
                (new BankTransaction())
                    ->setAmount(new Money($mappedAmount))
                    ->setCreatedAt(new DateTime($mappedAt))
                    ->setState(BankTransaction::STATE_COMPLETE)
                    ->setType(BankTransaction::TYPE_MERCHANT_PAYMENT)
                    ->setTransactionUuid($transactionUuid)
                    ->setDebtorName($debtorName),
            ],
            'debtor payment without: mapped_amount, mapped_at, transaction_uuid, debtor_name' => [
                [
                    'pending_amount' => $pendingAmount,
                    'created_at' => $createdAt,
                    'payment_type' => BankTransaction::TYPE_INVOICE_PAYBACK,
                ],
                (new BankTransaction())
                    ->setAmount(new Money($pendingAmount))
                    ->setCreatedAt(new DateTime($createdAt))
                    ->setState(BankTransaction::STATE_NEW)
                    ->setType(BankTransaction::TYPE_INVOICE_PAYBACK)
                    ->setTransactionUuid(null)
                    ->setDebtorName(null),
            ],
        ];
    }
}
