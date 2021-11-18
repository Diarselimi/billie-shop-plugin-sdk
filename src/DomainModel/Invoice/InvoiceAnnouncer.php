<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Invoice\CreateInvoice;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceAnnouncer implements LoggingInterface
{
    private const SERVICES = ['financing', 'dci'];

    use LoggingTrait;

    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function announce(
        Invoice $invoice,
        string $orderUuid,
        string $debtorCompanyName,
        string $orderExternalCode,
        ?UuidInterface $debtorSepaMandateUuid,
        ?UuidInterface $investorUuid
    ): void {
        $trackingUrl = $invoice->getShippingInfo() ? $invoice->getShippingInfo()->getTrackingUrl() : null;
        $message = (new CreateInvoice())
            ->setUuid($invoice->getUuid())
            ->setOrderUuid($orderUuid)
            ->setCustomerUuid($invoice->getCustomerUuid())
            ->setDebtorCompanyUuid($invoice->getDebtorCompanyUuid())
            ->setDebtorCompanyName($debtorCompanyName)
            ->setPaymentDebtorUuid($invoice->getPaymentDebtorUuid())
            ->setPaymentUuid($invoice->getPaymentUuid())
            ->setGrossAmount($invoice->getAmount()->getGross()->shift(2)->toInt())
            ->setNetAmount($invoice->getAmount()->getNet()->shift(2)->toInt())
            ->setGrossFeeAmount($invoice->getFeeAmount()->getGross()->shift(2)->toInt())
            ->setNetFeeAmount($invoice->getFeeAmount()->getNet()->shift(2)->toInt())
            ->setTaxFeeAmount($invoice->getFeeAmount()->getTax()->shift(2)->toInt())
            ->setFeeRate($invoice->getFeeRate()->shift(2)->toInt())
            ->setDuration($invoice->getDuration())
            ->setBillingDate($invoice->getBillingDate()->format('Y-m-d'))
            ->setProofOfDeliveryUrl($trackingUrl)
            ->setServices(self::SERVICES)
            ->setExternalCode($invoice->getExternalCode())
            ->setOrderExternalCode($orderExternalCode)
            ->setDebtorSepaMandateUuid($debtorSepaMandateUuid ? $debtorSepaMandateUuid->toString() : null)
            ->setInvestorUuid($investorUuid ? $investorUuid->toString() : null)
        ;

        $this->bus->dispatch($message);
        $this->logInfo('CreateInvoice event announced');
    }
}
