<?php

declare(strict_types=1);

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorSettings\DebtorSettingsEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;
use Prophecy\Prophecy\MethodProphecy;

trait RiskCheckSpecHelperTrait
{
    private function passCheckResult(string $riskCheckName): CheckResult
    {
        return new CheckResult(true, $riskCheckName);
    }

    private function notPassCheckResult(string $riskCheckName): CheckResult
    {
        return new CheckResult(false, $riskCheckName);
    }

    /**
     * @param bool           $isTrustedSource
     * @param OrderContainer $orderContainer
     */
    private function setDebtorIsTrustedSourceFlag(bool $isTrustedSource, $orderContainer)
    {
        $debtorCompany = new DebtorCompany();
        $debtorCompany->setIsTrustedSource($isTrustedSource);

        /** @var MethodProphecy $prophecy */
        $prophecy = $orderContainer->getDebtorCompany();
        $prophecy->willReturn($debtorCompany);
    }

    /**
     * @param bool           $isWhitelisted
     * @param OrderContainer $orderContainer
     */
    private function setDebtorIsWhitelistedSourceFlag(bool $isWhitelisted, $orderContainer)
    {
        $debtorSettings = new DebtorSettingsEntity();
        $debtorSettings->setIsWhitelisted($isWhitelisted);

        /** @var MethodProphecy $prophecy */
        $prophecy = $orderContainer->getDebtorSettings();
        $prophecy->willReturn($debtorSettings);
    }
}
