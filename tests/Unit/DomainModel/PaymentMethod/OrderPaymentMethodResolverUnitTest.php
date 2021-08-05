<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\PaymentMethod;

use App\DomainModel\BankAccount\BankAccountServiceException;
use App\DomainModel\BankAccount\BankAccountServiceInterface;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\DebtorPaymentDetailsDTO;
use App\DomainModel\PaymentMethod\OrderPaymentMethodResolver;
use App\DomainModel\PaymentMethod\PaymentMethod;
use App\Test\TestSentryClient;
use App\Tests\Unit\UnitTestCase;
use Ozean12\BancoSDK\Model\Bank;
use Ozean12\Borscht\Client\DomainModel\BorschtClientInterface;
use Ozean12\Borscht\Client\DomainModel\DirectDebit\DirectDebit;
use Ozean12\Borscht\Client\DomainModel\Ticket\Ticket;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandate;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Exception\InvalidIbanException;
use Ozean12\Support\ValueObject\Iban;
use Prophecy\Argument;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;

class OrderPaymentMethodResolverUnitTest extends UnitTestCase
{
    /**
     * @var BankAccountServiceInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $bankAccountService;

    /**
     * @var SepaClientInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $sepaClient;

    /**
     * @var BorschtClientInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $borschtClient;

    protected function setUp(): void
    {
        $this->bankAccountService = $this->prophesize(BankAccountServiceInterface::class);
        $this->sepaClient = $this->prophesize(SepaClientInterface::class);
        $this->borschtClient = $this->prophesize(BorschtClientInterface::class);
    }

    private function createResolver(): OrderPaymentMethodResolver
    {
        $resolver = new OrderPaymentMethodResolver(
            $this->bankAccountService->reveal(),
            $this->sepaClient->reveal(),
            $this->borschtClient->reveal()
        );
        $resolver->setLogger(new NullLogger());
        $resolver->setSentry(new TestSentryClient());

        return $resolver;
    }

    /**
     * @test
     */
    public function shouldReturnEmptyCollectionIfOrderHasNoDebtor(): void
    {
        $resolver = $this->createResolver();

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn(
            (new OrderEntity())
                ->setMerchantDebtorId(null)
                ->setWorkflowName(OrderEntity::WORKFLOW_NAME_V2)
        );

        $this->assertCount(0, $resolver->getOrderPaymentMethods($orderContainer->reveal()));
    }

    /**
     * @test
     */
    public function shouldReturnBankTransferMethod(): void
    {
        $resolver = $this->createResolver();

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn(
            (new OrderEntity())
                ->setMerchantDebtorId(1)
                ->setWorkflowName(OrderEntity::WORKFLOW_NAME_V2)
        );

        $debtorPaymentDetails = new DebtorPaymentDetailsDTO();
        $debtorPaymentDetails->setBankAccountIban('DE27500105171416939916');
        $debtorPaymentDetails->setBankAccountBic('BICXXXX');
        $debtorPaymentDetails->setOutstandingAmount(0);

        $this->bankAccountService->getBankByBic('BICXXXX')
            ->shouldBeCalledOnce()
            ->willReturn(new Bank(['name' => 'Test Bank']));

        $orderContainer->getDebtorPaymentDetails()->willReturn($debtorPaymentDetails);

        $paymentMethods = $resolver->getOrderPaymentMethods($orderContainer->reveal());
        $this->assertCount(1, $paymentMethods);

        /** @var PaymentMethod $bankTransfer */
        $bankTransfer = $paymentMethods->first();
        $this->assertEquals(PaymentMethod::TYPE_BANK_TRANSFER, $bankTransfer->getType());
        $this->assertEquals('DE27500105171416939916', $bankTransfer->getBankAccount()->getIban()->toString());
        $this->assertEquals('BICXXXX', $bankTransfer->getBankAccount()->getBic());
        $this->assertEquals('Test Bank', $bankTransfer->getBankAccount()->getBankName());
    }

    /**
     * @test
     */
    public function shouldHaveNullBankNameIfBancoApiCallFails(): void
    {
        $resolver = $this->createResolver();

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn(
            (new OrderEntity())
                ->setMerchantDebtorId(1)
                ->setWorkflowName(OrderEntity::WORKFLOW_NAME_V2)
        );

        $debtorPaymentDetails = new DebtorPaymentDetailsDTO();
        $debtorPaymentDetails->setBankAccountIban('DE27500105171416939916');
        $debtorPaymentDetails->setBankAccountBic('BICXXXX');
        $debtorPaymentDetails->setOutstandingAmount(0);

        $this->bankAccountService->getBankByBic('BICXXXX')
            ->shouldBeCalledOnce()
            ->willThrow(BankAccountServiceException::class);

        $orderContainer->getDebtorPaymentDetails()->willReturn($debtorPaymentDetails);

        $paymentMethods = $resolver->getOrderPaymentMethods($orderContainer->reveal());
        $this->assertCount(1, $paymentMethods);

        /** @var PaymentMethod $bankTransfer */
        $bankTransfer = $paymentMethods->first();
        $this->assertEquals(PaymentMethod::TYPE_BANK_TRANSFER, $bankTransfer->getType());
        $this->assertEquals('DE27500105171416939916', $bankTransfer->getBankAccount()->getIban()->toString());
        $this->assertEquals('BICXXXX', $bankTransfer->getBankAccount()->getBic());
        $this->assertEquals(null, $bankTransfer->getBankAccount()->getBankName());
    }

    /**
     * @test
     */
    public function shouldNotGetDirectDebitInfoFromFirstInvoiceForOrdersV2(): void
    {
        $sepaMandateUuid = Uuid::uuid4();
        $resolver = $this->createResolver();

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn(
            (new OrderEntity())
                ->setMerchantDebtorId(1)
                ->setDebtorSepaMandateUuid($sepaMandateUuid)
                ->setWorkflowName(OrderEntity::WORKFLOW_NAME_V2)
        );

        $invoice = new Invoice();
        $invoice->setPaymentUuid(Uuid::uuid4()->toString());

        $orderContainer->getInvoices()->willReturn(
            new InvoiceCollection([$invoice])
        );

        $this->sepaClient->getMandate($sepaMandateUuid)->shouldBeCalledOnce()->willReturn(
            new SepaMandate(
                $sepaMandateUuid,
                'REF',
                'CREDITOR',
                true,
                new BankAccount(new Iban('DE27500105171416939916'), 'BICXXXX', null, null)
            )
        );

        $this->bankAccountService->getBankByBic(Argument::any())->shouldNotBeCalled();

        $paymentMethods = $resolver->getOrderPaymentMethods($orderContainer->reveal());
        $this->assertCount(1, $paymentMethods);

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $paymentMethods->first();
        $this->assertEquals(PaymentMethod::TYPE_DIRECT_DEBIT, $paymentMethod->getType());
        $this->assertEquals('DE27500105171416939916', $paymentMethod->getBankAccount()->getIban()->toString());
        $this->assertEquals('BICXXXX', $paymentMethod->getBankAccount()->getBic());
        $this->assertEquals(null, $paymentMethod->getBankAccount()->getBankName());
    }

    /**
     * @test
     */
    public function shouldGetDirectDebitInfoFromFirstInvoiceForOrdersV1(): void
    {
        $sepaMandateUuid = Uuid::uuid4();
        $resolver = $this->createResolver();

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn(
            (new OrderEntity())
                ->setMerchantDebtorId(1)
                ->setDebtorSepaMandateUuid($sepaMandateUuid)
                ->setWorkflowName(OrderEntity::WORKFLOW_NAME_V1)
        );

        $ticketUuid = Uuid::uuid4();
        $invoice = new Invoice();
        $invoice->setPaymentUuid($ticketUuid->toString());

        $orderContainer->getInvoices()->willReturn(
            new InvoiceCollection([$invoice])
        );

        $this->sepaClient->getMandate($sepaMandateUuid)->shouldBeCalledOnce()->willReturn(
            new SepaMandate(
                $sepaMandateUuid,
                'REF',
                'CREDITOR',
                true,
                new BankAccount(new Iban('DE27500105171416939916'), 'BICXXXX', null, null)
            )
        );

        $this->borschtClient->getTicket($ticketUuid)
            ->shouldBeCalledOnce()
            ->willReturn(
                new Ticket(
                    $ticketUuid,
                    new DirectDebit(
                        DirectDebit::STATE_NEW,
                        new \DateTime('2020-01-01 00:00:00'),
                        $sepaMandateUuid
                    )
                )
            );

        $this->bankAccountService->getBankByBic(Argument::any())->shouldNotBeCalled();

        $paymentMethods = $resolver->getOrderPaymentMethods($orderContainer->reveal());
        $this->assertCount(1, $paymentMethods);

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $paymentMethods->first();
        $this->assertEquals(PaymentMethod::TYPE_DIRECT_DEBIT, $paymentMethod->getType());
        $this->assertEquals('DE27500105171416939916', $paymentMethod->getBankAccount()->getIban()->toString());
        $this->assertEquals('BICXXXX', $paymentMethod->getBankAccount()->getBic());
        $this->assertEquals(null, $paymentMethod->getBankAccount()->getBankName());
        $this->assertNotNull($paymentMethod->getSepaMandate());
        $this->assertNotNull($paymentMethod->getSepaMandateExecutionDate());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyPaymentMethodsIfIbanIsInvalid(): void
    {
        $resolver = $this->createResolver();

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn(
            (new OrderEntity())
                ->setMerchantDebtorId(1)
                ->setWorkflowName(OrderEntity::WORKFLOW_NAME_V2)
        );

        $debtorPaymentDetails = new DebtorPaymentDetailsDTO();
        $debtorPaymentDetails->setBankAccountIban('DE27500105171416939916');
        $debtorPaymentDetails->setBankAccountBic('BICXXXX');
        $debtorPaymentDetails->setOutstandingAmount(0);

        $this->bankAccountService->getBankByBic('BICXXXX')
            ->shouldBeCalledOnce()
            ->willThrow(InvalidIbanException::class);

        $orderContainer->getDebtorPaymentDetails()->willReturn($debtorPaymentDetails);

        $paymentMethods = $resolver->getOrderPaymentMethods($orderContainer->reveal());
        $this->assertTrue($paymentMethods->isEmpty());
    }
}
