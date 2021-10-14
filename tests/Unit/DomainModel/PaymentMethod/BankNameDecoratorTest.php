<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\PaymentMethod;

use App\DomainModel\PaymentMethod\BankNameDecorator;
use App\DomainModel\PaymentMethod\BankNameResolver;
use App\DomainModel\PaymentMethod\PaymentMethod;
use App\Tests\Unit\UnitTestCase;
use Ozean12\InvoiceButler\Client\DomainModel\PaymentMethod\PaymentMethod as ClientPaymentMethod;
use Ozean12\InvoiceButler\Client\DomainModel\PaymentMethod\PaymentMethodCollection as ClientCollection;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Iban;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class BankNameDecoratorTest extends UnitTestCase
{
    private ObjectProphecy $bankAccountNameResolver;

    private ObjectProphecy $logger;

    private BankNameDecorator $bankNameDecorator;

    public function setUp(): void
    {
        $this->bankAccountNameResolver = $this->prophesize(BankNameResolver::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->bankNameDecorator = new BankNameDecorator($this->bankAccountNameResolver->reveal());

        $this->bankNameDecorator->setLogger($this->logger->reveal());
    }

    /** @test */
    public function shouldDecorateBankName(): void
    {
        $bankAccount = new BankAccount(
            new Iban('DE12500105179542622426'),
            'INGDDEFFXXX',
            null,
            null
        );
        $bankName = 'Mocked Bank Name GmbH';
        $clientPaymentMethod = new ClientPaymentMethod(
            ClientPaymentMethod::TYPE_BANK_TRANSFER,
            $bankAccount,
            null,
            null
        );
        $clientCollection = new ClientCollection([$clientPaymentMethod]);
        $this->bankAccountNameResolver->resolve($bankAccount)
            ->willReturn(
                new BankAccount(
                    new Iban('DE12500105179542622426'),
                    'INGDDEFFXXX',
                    $bankName,
                    null
                )
            );

        $paymentMethods = $this->bankNameDecorator->addBankName($clientCollection);
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $paymentMethods->toArray()[0];

        self::assertEquals($bankName, $paymentMethod->getBankAccount()->getBankName());
    }
}
