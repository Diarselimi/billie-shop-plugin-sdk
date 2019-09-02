<?php

namespace App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class RestDebtorLimitManager implements DebtorLimitManagerInterface, LoggingInterface
{
    use LoggingTrait;

    private $companyService;

    public function __construct(CompaniesServiceInterface $companyService)
    {
        $this->companyService = $companyService;
    }

    public function lockDebtorLimit(string $debtorUuid, float $amount): void
    {
        $this->logInfo('Unlock debtor limit with deprecated rest strategy');

        try {
            $this->companyService->lockDebtorLimit($debtorUuid, $amount);
        } catch (CompaniesServiceRequestException $exception) {
            throw new MerchantDebtorLimitsException("Company service call was unsuccessful", null, $exception);
        }
    }

    public function unlockDebtorLimit(string $debtorUuid, float $amount): void
    {
        $this->logInfo('Unlock debtor limit with deprecated rest strategy');

        try {
            $this->companyService->unlockDebtorLimit($debtorUuid, $amount);
        } catch (CompaniesServiceRequestException $exception) {
            throw new MerchantDebtorLimitsException("Company service call was unsuccessful", null, $exception);
        }
    }
}
