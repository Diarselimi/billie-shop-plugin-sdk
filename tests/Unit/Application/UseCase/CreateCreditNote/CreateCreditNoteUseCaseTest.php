<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\CreateCreditNote;

use App\Application\UseCase\CreateCreditNote\CreateCreditNoteRequest;
use App\Application\UseCase\CreateCreditNote\CreateCreditNoteUseCase;
use App\DomainModel\DebtorCompany\Company;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteCreationService;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\Lifecycle\OrderTerminalStateChangeService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class CreateCreditNoteUseCaseTest extends UnitTestCase
{
    private ObjectProphecy $creditNoteFactory;

    private ObjectProphecy $orderContainerFactory;

    private ObjectProphecy $creditNoteCreationService;

    private CreateCreditNoteUseCase $useCase;

    private ObjectProphecy $orderTerminalStateChange;

    private ObjectProphecy $logger;

    public function setUp(): void
    {
        $this->creditNoteFactory = $this->prophesize(CreditNoteFactory::class);
        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->orderTerminalStateChange = $this->prophesize(OrderTerminalStateChangeService::class);
        $this->creditNoteCreationService = $this->prophesize(CreditNoteCreationService::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->useCase = new CreateCreditNoteUseCase(
            $this->creditNoteFactory->reveal(),
            $this->orderContainerFactory->reveal(),
            $this->creditNoteCreationService->reveal(),
            $this->orderTerminalStateChange->reveal()
        );

        $this->useCase->setValidator($this->createFakeValidator());
        $this->useCase->setLogger($this->logger->reveal());
    }

    /** @test */
    public function shouldCallCreditNoteCreationServiceCorrectly(): void
    {
        $invoiceUuid = Uuid::uuid4()->toString();
        $merchantId = 1;
        $externalCode = 'someExternalCode';
        $externalComment = 'someExternalComment';
        $amount = new TaxedMoney(new Money(100), new Money(81), new Money(19));
        $orderContainer = $this->prophesize(OrderContainer::class);
        $invoice = new Invoice();
        $invoice->setUuid($invoiceUuid)
            ->setAmount($amount);

        $orderContainer->getDebtorCompany()->willReturn((new Company())->setUuid($invoiceUuid));
        $orderContainer->getOrder()->willReturn((new OrderEntity())->setWorkflowName('order_v2'));

        $orderContainer->getInvoices()->willReturn(new InvoiceCollection([$invoice]));
        $this
            ->orderContainerFactory
            ->loadByInvoiceUuidAndMerchantId($invoiceUuid, $merchantId)
            ->willReturn($orderContainer->reveal());
        $this->creditNoteFactory->create(
            $invoice,
            $amount,
            $externalCode,
            null
        )->willReturn(
            (new CreditNote())
                ->setUuid($invoiceUuid)
                ->setAmount(TaxedMoneyFactory::create(100, 81, 19))
        );

        $this
            ->creditNoteCreationService
            ->create($invoice, Argument::that(function (CreditNote $creditNote) use ($externalComment) {
                return $creditNote->getExternalComment() === $externalComment;
            }))
            ->shouldBeCalledOnce();

        $request = (new CreateCreditNoteRequest())
            ->setInvoiceUuid($invoiceUuid)
            ->setMerchantId($merchantId)
            ->setExternalCode($externalCode)
            ->setExternalComment($externalComment)
            ->setAmount($amount);
        $this->useCase->execute($request);
    }
}
