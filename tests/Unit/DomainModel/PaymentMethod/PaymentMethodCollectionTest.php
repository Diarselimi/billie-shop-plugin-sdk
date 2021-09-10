<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\PaymentMethod;

use App\DomainModel\PaymentMethod\PaymentMethod;
use App\DomainModel\PaymentMethod\PaymentMethodCollection;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Iban;

class PaymentMethodCollectionTest extends UnitTestCase
{
    public function testShouldReturnDirectDebitWhenPresent(): void
    {
        $paymentMethods = new PaymentMethodCollection([
            new PaymentMethod(
                PaymentMethod::TYPE_DIRECT_DEBIT,
                $this->createBankAccount(),
                null,
                null
            ),
            new PaymentMethod(
                PaymentMethod::TYPE_BANK_TRANSFER,
                $this->createBankAccount(),
                null,
                null
            ),
        ]);
        self::assertEquals(PaymentMethod::TYPE_DIRECT_DEBIT, $paymentMethods->getSelectedPaymentMethod());
    }

    public function testShouldReturnBankTransferWhenNoDirectDebitPresent(): void
    {
        $paymentMethods = new PaymentMethodCollection([
            new PaymentMethod(
                PaymentMethod::TYPE_BANK_TRANSFER,
                $this->createBankAccount(),
                null,
                null
            ),
        ]);
        self::assertEquals(PaymentMethod::TYPE_BANK_TRANSFER, $paymentMethods->getSelectedPaymentMethod());
    }

    public function testShouldReturnNullWhenEmpty(): void
    {
        self::assertNull((new PaymentMethodCollection())->getSelectedPaymentMethod());
    }

    private function createBankAccount(): BankAccount
    {
        return new BankAccount(
            new Iban('DE12500105179542622426'),
            'INGDDEFFXXX',
            null,
            null
        );
    }
}
