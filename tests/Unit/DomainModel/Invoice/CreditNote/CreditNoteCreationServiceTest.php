<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\Invoice\CreditNote;

use App\DomainModel\DebtorCompany\Company;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteAmountExceededException;
use App\DomainModel\Invoice\CreditNote\CreditNoteAmountTaxExceededException;
use App\DomainModel\Invoice\CreditNote\CreditNoteCreationService;
use App\DomainModel\Invoice\CreditNote\CreditNoteNotAllowedException;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceContainer;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @see CreditNoteCreationService
 */
class CreditNoteCreationServiceTest extends UnitTestCase
{
    /**
     * @var InvoiceServiceInterface|ObjectProphecy
     */
    private ObjectProphecy $invoiceService;

    private CreditNoteCreationService $service;

    /**
     * @var InvoiceContainer|ObjectProphecy
     */
    private ObjectProphecy $orderContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getDebtorCompany()->willReturn((new Company())->setUuid('test'));

        $this->invoiceService = $this->prophesize(InvoiceServiceInterface::class);
        $this->service = new CreditNoteCreationService($this->invoiceService->reveal());
    }

    /**
     * @test
     */
    public function shouldFailIfOrderWorkflowIsNotV2(): void
    {
        $this->orderContainer->getOrder()->willReturn(
            (new OrderEntity())->setWorkflowName(OrderEntity::WORKFLOW_NAME_V1)
        );

        $this->expectException(CreditNoteNotAllowedException::class);

        $invoice = new Invoice();
        $invoice->setState('complete')
            ->setMerchantPendingPaymentAmount(new Money(0))
            ->setAmount(TaxedMoneyFactory::create(100, 99, 1));
        $this->service->create($invoice, new CreditNote());
    }

    /**
     * @test
     * @dataProvider unsupportedInvoiceStateDataProvider
     * @param string $invoiceState
     */
    public function shouldFailIfInvoiceStateIsNotSupported(string $invoiceState): void
    {
        $invoice = new Invoice();
        $invoice->setState('complete')
            ->setMerchantPendingPaymentAmount(new Money(0))
            ->setAmount(TaxedMoneyFactory::create(100, 99, 1));

        $this->expectException(CreditNoteNotAllowedException::class);

        $this->service->create($invoice, new CreditNote());
    }

    public function unsupportedInvoiceStateDataProvider(): array
    {
        return [
            [Invoice::STATE_CANCELED],
            [Invoice::STATE_COMPLETE],
        ];
    }

    /**
     * @test
     */
    public function shouldFailIfAmountIsGreaterThanMaxAmountAmount(): void
    {
        $invoice = new Invoice();
        $invoice->setState('new')
            ->setMerchantPendingPaymentAmount(new Money(0))
            ->setAmount(TaxedMoneyFactory::create(100, 99, 1))
            ->setOutstandingAmount(new Money(40));

        $creditNote = (new CreditNote())->setAmount(
            new TaxedMoney(new Money(500), new Money(0), new Money(0))
        );

        $this->expectException(CreditNoteAmountExceededException::class);

        $this->service->create($invoice, $creditNote);
    }

    /**
     * @test
     */
    public function shouldFailIfAmountTaxIsGreaterThanInvoiceTax(): void
    {
        $invoice = (new Invoice())
            ->setState(Invoice::STATE_PAID_OUT)
            ->setOutstandingAmount(new Money(70))
            ->setMerchantPendingPaymentAmount(new Money(0))
            ->setAmount(new TaxedMoney(new Money(100), new Money(90), new Money(10)));

        $creditNote = (new CreditNote())->setAmount(
            new TaxedMoney(new Money(50), new Money(30), new Money(20))
        );

        $this->expectException(CreditNoteAmountTaxExceededException::class);

        $this->service->create($invoice, $creditNote);
    }
}
