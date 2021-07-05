<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\CreateCreditNote;

use App\Application\UseCase\CreateCreditNote\CreateCreditNoteRequest;
use App\Application\UseCase\CreateCreditNote\CreateCreditNoteUseCase;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteCreationService;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceContainer;
use App\DomainModel\Invoice\InvoiceContainerFactory;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;

class CreateCreditNoteUseCaseTest extends UnitTestCase
{
    private ObjectProphecy $creditNoteFactory;

    private ObjectProphecy $invoiceContainerFactory;

    private ObjectProphecy $creditNoteCreationService;

    private CreateCreditNoteUseCase $useCase;

    public function setUp(): void
    {
        $this->creditNoteFactory = $this->prophesize(CreditNoteFactory::class);
        $this->invoiceContainerFactory = $this->prophesize(InvoiceContainerFactory::class);
        $this->creditNoteCreationService = $this->prophesize(CreditNoteCreationService::class);

        $this->useCase = new CreateCreditNoteUseCase(
            $this->creditNoteFactory->reveal(),
            $this->invoiceContainerFactory->reveal(),
            $this->creditNoteCreationService->reveal()
        );

        $this->useCase->setValidator($this->createFakeValidator());
    }

    /**
     * @test
     */
    public function shouldCallCreditNoteCreationServiceCorrectly(): void
    {
        $invoiceUuid = Uuid::uuid4()->toString();
        $merchantId = 1;
        $externalCode = 'someExternalCode';
        $externalComment = 'someExternalComment';
        $amount = new TaxedMoney(new Money(100), new Money(81), new Money(19));
        $invoiceContainer = $this->prophesize(InvoiceContainer::class);
        $invoice = new Invoice();
        $invoiceContainer->getInvoice()->willReturn($invoice);
        $this
            ->invoiceContainerFactory
            ->createFromInvoiceAndMerchant($invoiceUuid, $merchantId)
            ->willReturn($invoiceContainer->reveal());
        $this->creditNoteFactory->create(
            $invoice,
            $amount,
            $externalCode,
            null
        )->willReturn(new CreditNote());

        $this
            ->creditNoteCreationService
            ->create($invoiceContainer, Argument::that(function (CreditNote $creditNote) use ($externalComment) {
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
