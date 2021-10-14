<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\PaymentMethod;

use App\DomainModel\PaymentMethod\BankNameResolver;
use App\DomainModel\PaymentMethod\BankTransactionPaymentMethodResolver;
use App\DomainModel\PaymentMethod\PaymentMethod;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransaction;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransactionTicket;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransactionTicketCollection;
use Ozean12\Borscht\Client\DomainModel\BorschtClientInterface;
use Ozean12\Borscht\Client\DomainModel\Debtor\Debtor;
use Ozean12\Borscht\Client\DomainModel\DirectDebit\DirectDebit;
use Ozean12\Money\Money;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandate;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Iban;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @covers BankTransactionPaymentMethodResolver
 */
class BankTransactionPaymentMethodResolverUnitTest extends UnitTestCase
{
    /**
     * @var BankNameResolver|\Prophecy\Prophecy\ObjectProphecy
     */
    private $bankAccountNameResolver;

    /**
     * @var SepaClientInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $sepaService;

    /**
     * @var BorschtClientInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $borschtService;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|LoggerInterface
     */
    private $logger;

    private BankTransactionPaymentMethodResolver $resolver;

    public function setUp(): void
    {
        $this->bankAccountNameResolver = $this->prophesize(BankNameResolver::class);
        $this->sepaService = $this->prophesize(SepaClientInterface::class);
        $this->borschtService = $this->prophesize(BorschtClientInterface::class);

        $this->resolver = new BankTransactionPaymentMethodResolver(
            $this->bankAccountNameResolver->reveal(),
            $this->sepaService->reveal(),
            $this->borschtService->reveal()
        );

        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->resolver->setLogger($this->logger->reveal());
    }

    public function testGetPaymentMethodReturnsNullIfDebtorPaymentUuidIsNull()
    {
        self::assertNull($this->resolver->getPaymentMethod(Uuid::uuid4(), null));
    }

    public function testGetPaymentMethodReturnsNullIfTransactionNotAllocated()
    {
        $transactionUuid = Uuid::uuid4();
        $debtorPaymentUuid = Uuid::uuid4();

        $this->borschtService->getBankTransactionDetails($transactionUuid)->shouldBeCalledOnce()
            ->willReturn(
                new BankTransaction(
                    $transactionUuid,
                    false,
                    new Money(),
                    new BankTransactionTicketCollection()
                )
            );

        self::assertNull($this->resolver->getPaymentMethod($transactionUuid, $debtorPaymentUuid));
    }

    public function testGetPaymentMethodReturnsDirectDebit(): void
    {
        $transactionUuid = Uuid::uuid4();
        $debtorPaymentUuid = Uuid::uuid4();
        $ticketUuid = Uuid::uuid4();
        $sepaMandateUuid = Uuid::uuid4();
        $paidAmount = new Money(10);
        $directDebit = new DirectDebit(DirectDebit::STATE_COMPLETED, new \DateTimeImmutable(), $sepaMandateUuid, false);
        $bankAccount = new BankAccount(
            new Iban('DE27500105171416939916'),
            'BICXXXX',
            'Test Bank',
            null
        );
        $sepaMandate = new SepaMandate(
            $sepaMandateUuid,
            'REF',
            'CREDITOR',
            true,
            $bankAccount
        );

        $tickets = [
            new BankTransactionTicket($ticketUuid, $paidAmount, $directDebit),
        ];

        $transaction = new BankTransaction(
            $transactionUuid,
            true,
            new Money(),
            new BankTransactionTicketCollection($tickets)
        );

        $debtor = new Debtor($debtorPaymentUuid, $bankAccount);

        $this->borschtService->getBankTransactionDetails($transactionUuid)
            ->shouldBeCalledOnce()->willReturn($transaction);

        $this->borschtService->getDebtor($debtorPaymentUuid)
            ->shouldBeCalledOnce()->willReturn($debtor);

        $this->sepaService->getMandate($sepaMandateUuid)
            ->shouldBeCalledOnce()->willReturn($sepaMandate);

        $this->bankAccountNameResolver->resolve($bankAccount)
            ->shouldBeCalledOnce()->willReturn($bankAccount);

        $paymentMethod = $this->resolver->getPaymentMethod($transactionUuid, $debtorPaymentUuid);

        self::assertEquals(PaymentMethod::TYPE_DIRECT_DEBIT, $paymentMethod->getType());
        self::assertEquals($bankAccount, $paymentMethod->getBankAccount());
        self::assertEquals($sepaMandate, $paymentMethod->getSepaMandate());
    }

    public function testGetPaymentMethodReturnsBankTransfer(): void
    {
        $transactionUuid = Uuid::uuid4();
        $debtorPaymentUuid = Uuid::uuid4();
        $ticketUuid = Uuid::uuid4();
        $paidAmount = new Money(10);
        $bankAccount = new BankAccount(
            new Iban('DE27500105171416939916'),
            'BICXXXX',
            'Test Bank',
            null
        );

        $tickets = [
            new BankTransactionTicket($ticketUuid, $paidAmount, null),
        ];

        $transaction = new BankTransaction(
            $transactionUuid,
            true,
            new Money(),
            new BankTransactionTicketCollection($tickets)
        );

        $debtor = new Debtor($debtorPaymentUuid, $bankAccount);

        $this->borschtService->getBankTransactionDetails($transactionUuid)
            ->shouldBeCalledOnce()->willReturn($transaction);

        $this->borschtService->getDebtor($debtorPaymentUuid)
            ->shouldBeCalledOnce()->willReturn($debtor);

        $this->sepaService->getMandate(Argument::any())
            ->shouldNotBeCalled();

        $this->bankAccountNameResolver->resolve($bankAccount)
            ->shouldBeCalledOnce()->willReturn($bankAccount);

        $paymentMethod = $this->resolver->getPaymentMethod($transactionUuid, $debtorPaymentUuid);

        self::assertEquals(PaymentMethod::TYPE_BANK_TRANSFER, $paymentMethod->getType());
        self::assertEquals($bankAccount, $paymentMethod->getBankAccount());
        self::assertNull($paymentMethod->getSepaMandate());
    }

    public function testGetPaymentMethodReturnsBankTransferOnMixedMethodsAndLogsError(): void
    {
        $transactionUuid = Uuid::uuid4();
        $debtorPaymentUuid = Uuid::uuid4();
        $ticketUuid = Uuid::uuid4();
        $sepaMandateUuid = Uuid::uuid4();
        $paidAmount = new Money(10);
        $directDebit = new DirectDebit(DirectDebit::STATE_COMPLETED, new \DateTimeImmutable(), $sepaMandateUuid, false);
        $bankAccount = new BankAccount(
            new Iban('DE27500105171416939916'),
            'BICXXXX',
            'Test Bank',
            null
        );
        $sepaMandate = new SepaMandate(
            $sepaMandateUuid,
            'REF',
            'CREDITOR',
            true,
            $bankAccount
        );

        $tickets = [
            new BankTransactionTicket($ticketUuid, $paidAmount, $directDebit),
            new BankTransactionTicket($ticketUuid, $paidAmount, null),
        ];

        $transaction = new BankTransaction(
            $transactionUuid,
            true,
            new Money(),
            new BankTransactionTicketCollection($tickets)
        );

        $debtor = new Debtor($debtorPaymentUuid, $bankAccount);

        $this->borschtService->getBankTransactionDetails($transactionUuid)
            ->shouldBeCalledOnce()->willReturn($transaction);

        $this->borschtService->getDebtor($debtorPaymentUuid)
            ->shouldBeCalledOnce()->willReturn($debtor);

        $this->sepaService->getMandate($sepaMandateUuid)
            ->shouldBeCalledOnce()->willReturn($sepaMandate);

        $this->bankAccountNameResolver->resolve($bankAccount)
            ->shouldBeCalledTimes(2)->willReturn($bankAccount);

        $this->logger->error(Argument::cetera())->shouldBeCalledOnce();

        $paymentMethod = $this->resolver->getPaymentMethod($transactionUuid, $debtorPaymentUuid);

        self::assertEquals(PaymentMethod::TYPE_BANK_TRANSFER, $paymentMethod->getType());
        self::assertEquals($bankAccount, $paymentMethod->getBankAccount());
        self::assertNull($paymentMethod->getSepaMandate());
    }

    public function testGetPaymentMethodReturnsFirstMandateOnMixedSepaMandatesAndLogsError(): void
    {
        $transactionUuid = Uuid::uuid4();
        $debtorPaymentUuid = Uuid::uuid4();
        $ticketUuid = Uuid::uuid4();
        $sepaMandateUuid = Uuid::uuid4();
        $sepaMandateUuid2 = Uuid::uuid4();
        $paidAmount = new Money(10);
        $directDebit = new DirectDebit(DirectDebit::STATE_COMPLETED, new \DateTimeImmutable(), $sepaMandateUuid, false);
        $directDebit2 = new DirectDebit(
            DirectDebit::STATE_COMPLETED,
            new \DateTimeImmutable(),
            $sepaMandateUuid2,
            false
        );
        $bankAccount = new BankAccount(
            new Iban('DE27500105171416939916'),
            'BICXXXX',
            'Test Bank',
            null
        );
        $sepaMandate = new SepaMandate(
            $sepaMandateUuid,
            'REF',
            'CREDITOR',
            true,
            $bankAccount
        );

        $tickets = [
            new BankTransactionTicket($ticketUuid, $paidAmount, $directDebit),
            new BankTransactionTicket($ticketUuid, $paidAmount, $directDebit2),
        ];

        $transaction = new BankTransaction(
            $transactionUuid,
            true,
            new Money(),
            new BankTransactionTicketCollection($tickets)
        );

        $debtor = new Debtor($debtorPaymentUuid, $bankAccount);

        $this->borschtService->getBankTransactionDetails($transactionUuid)
            ->shouldBeCalledOnce()->willReturn($transaction);

        $this->borschtService->getDebtor($debtorPaymentUuid)
            ->shouldBeCalledOnce()->willReturn($debtor);

        $this->sepaService->getMandate($sepaMandateUuid)
            ->shouldBeCalledTimes(1)->willReturn($sepaMandate);

        $this->sepaService->getMandate($sepaMandateUuid2)
            ->shouldBeCalledTimes(1)->willReturn($sepaMandate);

        $this->bankAccountNameResolver->resolve($bankAccount)
            ->shouldBeCalledTimes(2)->willReturn($bankAccount);

        $this->logger->error(Argument::cetera())->shouldBeCalledOnce();

        $paymentMethod = $this->resolver->getPaymentMethod($transactionUuid, $debtorPaymentUuid);

        self::assertEquals(PaymentMethod::TYPE_DIRECT_DEBIT, $paymentMethod->getType());
        self::assertEquals($bankAccount, $paymentMethod->getBankAccount());
        self::assertEquals($sepaMandate, $paymentMethod->getSepaMandate());
    }
}
