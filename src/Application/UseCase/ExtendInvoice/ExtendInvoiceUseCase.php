<?php

namespace App\Application\UseCase\ExtendInvoice;

use App\Application\CommandHandler;
use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\Duration;
use App\DomainModel\Invoice\ExtendInvoiceService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Salesforce\ClaimStateService;

class ExtendInvoiceUseCase implements ValidatedUseCaseInterface, CommandHandler
{
    use ValidatedUseCaseTrait;

    private OrderContainerFactory $orderContainerFactory;

    private ClaimStateService $claimStateService;

    private ExtendInvoiceService $extendInvoiceService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        ExtendInvoiceService $extendInvoiceService,
        ClaimStateService $claimStateService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->extendInvoiceService = $extendInvoiceService;
        $this->claimStateService = $claimStateService;
    }

    public function execute(ExtendInvoiceRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(
                $request->getInvoiceUuid(),
                $request->getMerchantId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new InvoiceNotFoundException();
        }

        $invoice = $orderContainer->getInvoices()->get($request->getInvoiceUuid());
        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        $duration = new Duration($request->getDuration());

        if ($invoice->isComplete() || $invoice->isCanceled()) {
            throw new InvoiceNotExtendableException("Invoice state doesn't support extension");
        }

        if (!$invoice->canBeExtendedWith($duration)) {
            throw new InvoiceNotExtendableException('New duration should be longer than existing');
        }

        if ($invoice->isLate() && $this->claimStateService->isInCollection($invoice->getUuid())) {
            throw new InvoiceNotExtendableException('Invoice cannot be extended');
        }

        try {
            $this->extendInvoiceService->extend($orderContainer, $invoice, $duration->days());
        } catch (FeeCalculationException $exception) {
            throw new InvoiceNotExtendableException('Invoice cannot be extended due to fee calculation', null, $exception);
        }
    }
}
