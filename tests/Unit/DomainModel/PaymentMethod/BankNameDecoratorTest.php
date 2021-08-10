<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\PaymentMethod;

use App\DomainModel\BankAccount\BankAccountServiceException;
use App\DomainModel\BankAccount\BankAccountServiceInterface;
use App\DomainModel\PaymentMethod\BankNameDecorator;
use App\DomainModel\PaymentMethod\PaymentMethod;
use App\Tests\Unit\UnitTestCase;
use Ozean12\BancoSDK\Model\Bank;
use Ozean12\InvoiceButler\Client\DomainModel\PaymentMethod\PaymentMethod as ClientPaymentMethod;
use Ozean12\InvoiceButler\Client\DomainModel\PaymentMethod\PaymentMethodCollection as ClientCollection;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Iban;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class BankNameDecoratorTest extends UnitTestCase
{
    private ObjectProphecy $bankAccountService;

    private ObjectProphecy $logger;

    private BankNameDecorator $bankNameDecorator;

    public function setUp(): void
    {
        $this->bankAccountService = $this->prophesize(BankAccountServiceInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->bankNameDecorator = new BankNameDecorator($this->bankAccountService->reveal());

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
        $clientPaymentMethod = new ClientPaymentMethod(ClientPaymentMethod::TYPE_BANK_TRANSFER, $bankAccount);
        $clientCollection = new ClientCollection([$clientPaymentMethod]);
        $this->bankAccountService->getBankByBic($bankAccount->getBic())->willReturn(new Bank(['name' => $bankName]));

        $paymentMethods = $this->bankNameDecorator->addBankName($clientCollection);
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $paymentMethods->toArray()[0];

        self::assertEquals($bankName, $paymentMethod->getBankAccount()->getBankName());
    }

    /** @test */
    public function shouldLogWhenBankNotFound(): void
    {
        $bankAccount = new BankAccount(
            new Iban('DE12500105179542622426'),
            'INGDDEFFXXX',
            null,
            null
        );
        $clientPaymentMethod = new ClientPaymentMethod(ClientPaymentMethod::TYPE_BANK_TRANSFER, $bankAccount);
        $clientCollection = new ClientCollection([$clientPaymentMethod]);
        $this->bankAccountService
            ->getBankByBic(Argument::cetera())
            ->willThrow(BankAccountServiceException::class);

        $this->logger->debug(Argument::cetera())->shouldBeCalledOnce();

        $this->bankNameDecorator->addBankName($clientCollection);
    }
}
