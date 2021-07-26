<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateCreditNote;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteCreationService;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\CreditNote\CreditNoteNotAllowedException;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\Lifecycle\OrderTerminalStateChangeService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class CreateCreditNoteUseCase implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait, LoggingTrait;

    private CreditNoteFactory $creditNoteFactory;

    private CreditNoteCreationService $creditNoteCreationService;

    private OrderTerminalStateChangeService $orderTerminalStateChangeService;

    private OrderContainerFactory $orderContainerFactory;

    public function __construct(
        CreditNoteFactory $creditNoteFactory,
        OrderContainerFactory $orderContainerFactory,
        CreditNoteCreationService $creditNoteCreationService,
        OrderTerminalStateChangeService $orderTerminalStateChangeService
    ) {
        $this->creditNoteFactory = $creditNoteFactory;
        $this->creditNoteCreationService = $creditNoteCreationService;
        $this->orderTerminalStateChangeService = $orderTerminalStateChangeService;
        $this->orderContainerFactory = $orderContainerFactory;
    }

    public function execute(CreateCreditNoteRequest $request): CreditNote
    {
        $this->validateRequest($request);

        $orderContainer = $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(
            $request->getInvoiceUuid(),
            $request->getMerchantId()
        );

        $this->isWorkflowSupported($orderContainer);

        $invoice = $orderContainer->getInvoices()->get($request->getInvoiceUuid());
        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        $invoice->setPaymentDebtorUuid(
            $orderContainer->getDebtorCompany()->getUuid()
        );

        $creditNote = $this->creditNoteFactory->create(
            $invoice,
            $request->getAmount(),
            $request->getExternalCode(),
            null
        )->setExternalComment($request->getExternalComment());

        $this->creditNoteCreationService->create($invoice, $creditNote);

        $invoice->getCreditNotes()->add($creditNote);
        if ($invoice->getGrossAmount()->subtract($invoice->getCreditNotes()->getGrossSum())->isZero()) {
            $invoice->setState(Invoice::STATE_CANCELED);
            $this->logInfo('Invoice Moved to canceled.');
        }

        $this->orderTerminalStateChangeService->execute($orderContainer);

        return $creditNote;
    }

    private function isWorkflowSupported(OrderContainer $orderContainer): void
    {
        if (!$orderContainer->getOrder()->isWorkflowV2()) {
            throw new CreditNoteNotAllowedException(
                sprintf(
                    'Credit note creation not supported for order workflow: %s',
                    $orderContainer->getOrder()->getWorkflowName()
                )
            );
        }
    }
}
