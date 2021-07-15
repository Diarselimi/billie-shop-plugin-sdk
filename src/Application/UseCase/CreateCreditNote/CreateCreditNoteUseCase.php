<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateCreditNote;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteCreationService;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\InvoiceContainerFactory;

class CreateCreditNoteUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private CreditNoteFactory $creditNoteFactory;

    private InvoiceContainerFactory $invoiceContainerFactory;

    private CreditNoteCreationService $creditNoteCreationService;

    public function __construct(
        CreditNoteFactory $creditNoteFactory,
        InvoiceContainerFactory $invoiceContainerFactory,
        CreditNoteCreationService $creditNoteCreationService
    ) {
        $this->creditNoteFactory = $creditNoteFactory;
        $this->invoiceContainerFactory = $invoiceContainerFactory;
        $this->creditNoteCreationService = $creditNoteCreationService;
    }

    public function execute(CreateCreditNoteRequest $request): CreditNote
    {
        $this->validateRequest($request);

        $invoiceContainer = $this->invoiceContainerFactory->createFromInvoiceAndMerchant(
            $request->getInvoiceUuid(),
            $request->getMerchantId()
        );

        $creditNote = $this->creditNoteFactory->create(
            $invoiceContainer->getInvoice(),
            $request->getAmount(),
            $request->getExternalCode(),
            null
        )->setExternalComment($request->getExternalComment());

        $this->creditNoteCreationService->create($invoiceContainer, $creditNote);

        return $creditNote;
    }
}
