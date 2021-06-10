<?php

namespace App\Tests\Unit\Application\UseCase\ExtendInvoice;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\ExtendInvoice\ExtendInvoiceRequest;
use App\Application\UseCase\ExtendInvoice\ExtendInvoiceUseCase;
use App\DomainModel\Invoice\Duration;
use App\DomainModel\Invoice\ExtendInvoiceService;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\SalesforceInterface;
use App\Tests\Unit\UnitTestCase;
use DateTime;
use DomainException;
use InvalidArgumentException;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class ExtendInvoiceUseCaseTest extends UnitTestCase
{
    private const INVOICE_UUID = '72a74d7d-0bc7-45c3-8bb6-1e1d8aef1fc7';

    private const VALID_DURATION = 60;

    private const MERCHANT_ID = 12;

    /**
     * @var OrderContainerFactory|ObjectProphecy
     */
    private ObjectProphecy $orderContainerFactory;

    /**
     * @var OrderContainer|ObjectProphecy
     */
    private ObjectProphecy $orderContainer;

    /**
     * @var SalesforceInterface|ObjectProphecy
     */
    private ObjectProphecy $dciService;

    /**
     * @var ExtendInvoiceService|ObjectProphecy
     */
    private ObjectProphecy $extendInvoiceService;

    private ExtendInvoiceUseCase $extendInvoiceUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->orderContainer = $this->prophesize(OrderContainer::class);
        $this->extendInvoiceService = $this->prophesize(ExtendInvoiceService::class);
        $this->dciService = $this->prophesize(SalesforceInterface::class);
        $this->dciService->isDunningInProgress(Argument::type(Invoice::class))
            ->willReturn(false);

        $this->extendInvoiceUseCase = new ExtendInvoiceUseCase(
            $this->orderContainerFactory->reveal(),
            $this->extendInvoiceService->reveal(),
            $this->dciService->reveal()
        );
    }

    public function testItShouldFailIfInvoiceNotFound(): void
    {
        $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(self::INVOICE_UUID, self::MERCHANT_ID)
            ->willThrow(new OrderContainerFactoryException());
        $this->expectException(InvoiceNotFoundException::class);

        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, self::VALID_DURATION, self::MERCHANT_ID);
        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldFailIfDurationIsInvalid(): void
    {
        $this->orderContainer->getInvoices()->willReturn(new InvoiceCollection([self::INVOICE_UUID => $this->aLateInvoice()]));
        $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(self::INVOICE_UUID, self::MERCHANT_ID)
            ->willReturn($this->orderContainer);

        $invalidDuration = 125;
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, $invalidDuration, self::MERCHANT_ID);

        $this->expectException(InvalidArgumentException::class);

        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldFailWhenTheInvoiceIsCancelled()
    {
        $this->orderContainer->getInvoices()->willReturn(new InvoiceCollection([self::INVOICE_UUID => $this->aCancelledInvoice()]));

        $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(self::INVOICE_UUID, self::MERCHANT_ID)
            ->willReturn($this->orderContainer);

        $validDuration = self::VALID_DURATION;
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, $validDuration, self::MERCHANT_ID);

        $this->expectException(DomainException::class);

        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldFailWhenTheInvoiceIsComplete()
    {
        $this->orderContainer->getInvoices()->willReturn(new InvoiceCollection([self::INVOICE_UUID => $this->aCompleteInvoice()]));
        $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(self::INVOICE_UUID, self::MERCHANT_ID)
            ->willReturn($this->orderContainer);

        $validDuration = self::VALID_DURATION;
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, $validDuration, self::MERCHANT_ID);

        $this->expectException(DomainException::class);

        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldFailWhenDunningIsInProgress()
    {
        $invoice = $this->aCompleteInvoice();
        $this->orderContainer->getInvoices()->willReturn(new InvoiceCollection([self::INVOICE_UUID => $invoice]));
        $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(self::INVOICE_UUID, self::MERCHANT_ID)
            ->willReturn($this->orderContainer);

        $this->dciService->isDunningInProgress($invoice)
            ->willReturn(true);

        $validDuration = 35;
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, $validDuration, self::MERCHANT_ID);

        $this->expectException(DomainException::class);

        $this->extendInvoiceUseCase->execute($request);
    }

    public function testItShouldSucceed()
    {
        $request = new ExtendInvoiceRequest(self::INVOICE_UUID, self::VALID_DURATION, self::MERCHANT_ID);
        $duration = new Duration(self::VALID_DURATION);
        $invoice = $this->anInvoice();
        $this->orderContainer->getInvoices()->willReturn(new InvoiceCollection([self::INVOICE_UUID => $invoice]));

        $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(self::INVOICE_UUID, self::MERCHANT_ID)
            ->willReturn($this->orderContainer);

        $this->extendInvoiceService->extend($this->orderContainer, $invoice, $duration->days())->shouldBeCalled();

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

    private function aCompleteInvoice(): Invoice
    {
        return (new Invoice())->setUuid(self::INVOICE_UUID)
            ->setState(Invoice::STATE_COMPLETE);
    }
}
