<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\DebtorCompany\DebtorCompany;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Company\RequestSchufaB2BReport;
use Ozean12\Transfer\Message\Customer\CustomerCreated;
use Ozean12\Transfer\Message\Customer\CustomerFeeRatesUpdated;
use Symfony\Component\Messenger\MessageBusInterface;

class MerchantAnnouncer implements LoggingInterface
{
    use LoggingTrait;

    private const TYPE_MERCHANT = 'merchant';

    private const SCHUFA_B2B_REPORT_REASON = 'X1';

    private $bus;

    private $investorUuid;

    public function __construct(MessageBusInterface $bus, string $investorUuid)
    {
        $this->bus = $bus;
        $this->investorUuid = $investorUuid;
    }

    public function announceCustomerCreated(
        string $companyUuid,
        string $companyName,
        string $paymentUuid
    ): void {
        $message = (new CustomerCreated())
            ->setCompanyUuid($companyUuid)
            ->setName($companyName)
            ->setUuid($paymentUuid)
            ->setType(self::TYPE_MERCHANT)
            ->setInvestorUuid($this->investorUuid)
        ;

        $this->bus->dispatch($message);
        $this->logInfo('CustomerCreated event announced');
    }

    public function announceRequestSchufaB2BReport(DebtorCompany $debtorCompany): void
    {
        $message = (new RequestSchufaB2BReport())
            ->setCompanyUuid($debtorCompany->getUuid())
            ->setCompanyName($debtorCompany->getName())
            ->setReason(self::SCHUFA_B2B_REPORT_REASON)
            ->setHouse($debtorCompany->getAddressHouse())
            ->setStreet($debtorCompany->getAddressStreet())
            ->setPostalCode($debtorCompany->getAddressPostalCode())
            ->setCity($debtorCompany->getAddressCity())
            ->setCountry($debtorCompany->getAddressCountry())
        ;

        $this->bus->dispatch($message);
        $this->logInfo('RequestSchufaB2BReport event announced');
    }

    public function announceCustomerFeeRatesUpdated(string $merchantPaymentUuid, array $feeRates): void
    {
        $this->logInfo('Fee rates to be set', ['json' => json_encode($feeRates)]);

        $message = (new CustomerFeeRatesUpdated())
            ->setCompanyUuid($merchantPaymentUuid)
            ->setFeeRates($feeRates)
        ;

        $this->bus->dispatch($message);
        $this->logInfo('CustomerFeeRatesUpdated event announced');
    }
}
