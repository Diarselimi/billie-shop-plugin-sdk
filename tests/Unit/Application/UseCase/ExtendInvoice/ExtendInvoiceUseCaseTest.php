<?php

namespace App\Tests\Unit\Application\UseCase\ExtendInvoice;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\ExtendInvoice\ExtendInvoiceRequest;
use App\Application\UseCase\ExtendInvoice\ExtendInvoiceUseCase;
use App\DomainModel\Fee\Fee;
use App\DomainModel\Fee\FeeService;
use App\DomainModel\Invoice\Duration;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\Tests\Unit\UnitTestCase;
use DateTime;
use DomainException;
use InvalidArgumentException;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class ExtendInvoiceUseCaseTest extends UnitTestCase
{
    private const INVOICE_UUID = '72a74d7d-0bc7-45c3-8bb6-1e1d8aef1fc7';

    private const VALID_DURATION = 60;

    /**
     * @var InvoiceServiceInterface|ObjectProphecy
     */
    private ObjectProphecy $invoiceService;

    /**
     * @var FeeService|ObjectProphecy
     */
    private ObjectProphecy $feeService;

    /**
     * @var PaymentsServiceInterface|ObjectProphecy
     */
    private ObjectProphecy $paymentService;

    /**
     * @var SalesforceInterface|ObjectProphecy
     */
    private ObjectProphecy $dciService;

    private ExtendInvoiceUseCase $extendInvoiceUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = $this->prophesize(InvoiceServiceInterface::class);
        $this->feeService = $this->prophesize(FeeService::class);
        $this->paymentService = $this->prophesize(PaymentsServiceInterface::class);
        $this->dciService = $this->prophesize(SalesforceInterface::class);
        $this->dciService->isDunningInProgress(Argument::type(Invoice::class))
            ->willReturn(false);
        $this->extendInvoiceUseCase = new ExtendInvoiceUseCase(
            $this->invoiceService->reveal(),
            $this->feeService->reveal(),
            $this->paymentService->reveal(),
            $this->dciService->reveal()
        );
    }

    public function testItShouldFailIfInvoiceNotFound(): void
    {
        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)
            ->willReturn(null);
        $this->expectException(InvoiceNotFoundException::class);

        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, self::VALID_DURATION);
        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldFailIfDurationIsInvalid(): void
    {
        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)
            ->willReturn($this->anInvoice());

        $invalidDuration = 125;
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, $invalidDuration);

        $this->expectException(InvalidArgumentException::class);

        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldFailWhenTheInvoiceIsCancelled()
    {
        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)
            ->willReturn($this->aCancelledInvoice());

        $validDuration = self::VALID_DURATION;
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, $validDuration);

        $this->expectException(DomainException::class);

        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldFailWhenTheInvoiceIsComplete()
    {
        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)
            ->willReturn($this->aCompleteInvoice());

        $validDuration = self::VALID_DURATION;
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, $validDuration);

        $this->expectException(DomainException::class);

        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldFailWhenDunningIsInProgress()
    {
        $invoice = $this->aLateInvoice();
        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)
            ->willReturn($invoice);
        $this->dciService->isDunningInProgress($invoice)
            ->willReturn(true);

        $validDuration = 35;
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, $validDuration);

        $this->expectException(DomainException::class);

        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldSucceed()
    {
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, self::VALID_DURATION);
        $duration = new Duration(self::VALID_DURATION);
        $invoice = $this->anInvoice();
        $newFee = $this->aFee();

        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)
            ->willReturn($invoice);
        $this->paymentService->extendInvoiceDuration($invoice, $duration)
            ->shouldBeCalled();
        $this->feeService->getFee($invoice)
            ->willReturn($newFee);
        $this->invoiceService->extendInvoiceDuration($invoice, $newFee, $duration)
            ->shouldBeCalled();

        $this->extendInvoiceUseCase->execute($request);
    }

    private function anInvoice(): Invoice
    {
        $today = new DateTime();
        $nextWeek = $today->modify('+7 days');

        return (new Invoice())->setUuid(self::INVOICE_UUID)
            ->setState(Invoice::STATE_NEW)
            ->setDueDate($nextWeek)
            ->setDuration(15);
    }

    private function aCancelledInvoice(): Invoice
    {
        return (new Invoice())->setUuid(self::INVOICE_UUID)
            ->setState(Invoice::STATE_CANCELED);
    }

    private function aLateInvoice(): Invoice
    {
        $today = new DateTime();
        $lastWeek = $today->modify('-7 days');

        return (new Invoice())->setUuid(self::INVOICE_UUID)
            ->setState(Invoice::STATE_LATE)
            ->setDuration(15)
            ->setDueDate($lastWeek);
    }

    private function aFee(): Fee
    {
        $newFee = new Fee(new Percent(2), new Money(119), new Money(100), new Money(19));

        return $newFee;
    }

    private function aCompleteInvoice(): Invoice
    {
        return (new Invoice())->setUuid(self::INVOICE_UUID)
            ->setState(Invoice::STATE_COMPLETE);
    }
}
