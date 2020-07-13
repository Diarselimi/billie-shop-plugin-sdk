<?php

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorSettings\DebtorSettingsEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\CheckResult;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIsTrusted;
use PhpSpec\ObjectBehavior;

class DebtorIsTrustedSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(DebtorIsTrusted::class);
    }

    public function let(
        OrderContainer $orderContainer,
        MerchantDebtorEntity $merchantDebtor,
        DebtorCompany $debtorCompany,
        DebtorSettingsEntity $debtorSettings
    ) {
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);
        $orderContainer->getDebtorCompany()->willReturn($debtorCompany);
        $orderContainer->getDebtorSettings()->willReturn($debtorSettings);
    }

    public function it_returns_true_if_is_trusted_source(
        OrderContainer $orderContainer,
        DebtorCompany $debtorCompany
    ) {
        $debtorCompany->isTrustedSource()->willReturn(true);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(true, DebtorIsTrusted::NAME));
    }

    public function it_returns_false_if_debtor_is_not_whitelisted(
        OrderContainer $orderContainer,
        DebtorSettingsEntity $debtorSettings,
        DebtorCompany $debtorCompany
    ) {
        $debtorCompany->isTrustedSource()->willReturn(false);
        $debtorSettings->isWhitelisted()->willReturn(false);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(false, DebtorIsTrusted::NAME));
    }

    public function it_returns_true_if_debtor_is_whitelisted(
        OrderContainer $orderContainer,
        DebtorSettingsEntity $debtorSettings,
        DebtorCompany $debtorCompany
    ) {
        $debtorCompany->isTrustedSource()->willReturn(false);
        $debtorSettings->isWhitelisted()->willReturn(true);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(true, DebtorIsTrusted::NAME));
    }
}
