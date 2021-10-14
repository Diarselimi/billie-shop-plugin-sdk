<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\PaymentMethod;

use App\DomainModel\BankAccount\BankAccountServiceException;
use App\DomainModel\BankAccount\BankAccountServiceInterface;
use App\DomainModel\PaymentMethod\BankNameResolver;
use App\Tests\Unit\UnitTestCase;
use Ozean12\BancoSDK\Model\Bank;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Iban;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class BankNameResolverTest extends UnitTestCase
{
    private ObjectProphecy $bankAccountService;

    private ObjectProphecy $logger;

    private BankNameResolver $bankNameResolver;

    public function setUp(): void
    {
        $this->bankAccountService = $this->prophesize(BankAccountServiceInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->bankNameResolver = new BankNameResolver($this->bankAccountService->reveal());

        $this->bankNameResolver->setLogger($this->logger->reveal());
    }

    /** @test */
    public function shouldReturnBankAccountWithName(): void
    {
        $bankAccount = new BankAccount(
            new Iban('DE12500105179542622426'),
            'INGDDEFFXXX',
            null,
            null
        );
        $bankName = 'Mocked Bank Name GmbH';

        $this->bankAccountService->getBankByBic($bankAccount->getBic())->willReturn(new Bank(['name' => $bankName]));

        $resolved = $this->bankNameResolver->resolve($bankAccount);

        self::assertEquals($bankName, $resolved->getBankName());
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

        $this->bankAccountService
            ->getBankByBic(Argument::cetera())
            ->willThrow(BankAccountServiceException::class);

        $this->logger->debug(Argument::cetera())->shouldBeCalledOnce();

        $this->bankNameResolver->resolve($bankAccount);
    }
}
