<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

use App\DomainModel\Invoice\Invoice;
use App\Helper\Uuid\UuidGeneratorInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Transfer\Message\CreditNote\CreateCreditNote;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceCreditNoteAnnouncer implements LoggingInterface
{
    use LoggingTrait;

    private MessageBusInterface $bus;

    private UuidGeneratorInterface $uuidGenerator;

    public function __construct(MessageBusInterface $bus, UuidGeneratorInterface $uuidGenerator)
    {
        $this->bus = $bus;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(Invoice $invoice, TaxedMoney $amount, ?string $externalCode): void
    {
        $message = (new CreateCreditNote())
            ->setGrossAmount($amount->getGross()->shift(2)->toInt())
            ->setNetAmount($amount->getNet()->shift(2)->toInt())
            ->setInvoiceUuid($invoice->getUuid())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setInternalComment($externalCode)
        ;

        $this->bus->dispatch($message);
        $this->logInfo('CreateCreditNote event announced');
    }
}
