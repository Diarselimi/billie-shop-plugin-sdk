<?php

namespace App\DomainModel\Invoice;

use App\DomainModel\Fee\FeeCalculator;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Invoice\ExtendInvoice;
use Ozean12\Transfer\Shared\Invoice as InvoiceMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class ExtendInvoiceService implements LoggingInterface
{
    use LoggingTrait;

    private MessageBusInterface $bus;

    private FeeCalculator $feeCalculator;

    public function __construct(MessageBusInterface $bus, FeeCalculator $feeCalculator)
    {
        $this->bus = $bus;
        $this->feeCalculator = $feeCalculator;
    }

    public function extend(OrderContainer $orderContainer, Invoice $invoice, int $duration): void
    {
        $fee = $this->feeCalculator->calculate(
            $invoice->getAmount()->getGross(),
            $duration,
            $orderContainer->getMerchantSettings()->getFeeRates()
        );

        $invoiceMessage = (new InvoiceMessage())
            ->setUuid($invoice->getUuid())
            ->setDueDate((clone $invoice->getBillingDate())->modify("+ {$duration} days")->format('Y-m-d'))
            ->setFeeRate($fee->getFeeRate()->shift(2)->toInt())
            ->setNetFeeAmount($fee->getNetFeeAmount()->shift(2)->toInt())
            ->setVatOnFeeAmount($fee->getTaxFeeAmount()->shift(2)->toInt())
            ->setDuration($duration)
            ->setInvoiceReferences(
                ['external_code' => $invoice->getExternalCode()]
            );

        $extendInvoiceMessage = (new ExtendInvoice())
            ->setInvoice($invoiceMessage);

        $this->bus->dispatch($extendInvoiceMessage);
    }
}
