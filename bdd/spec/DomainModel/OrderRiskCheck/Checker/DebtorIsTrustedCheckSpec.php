<?php

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\DebtorSettings\DebtorSettingsEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIsTrustedCheck;
use PhpSpec\ObjectBehavior;

class DebtorIsTrustedCheckSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(DebtorIsTrustedCheck::class);
    }

    public function let(
        OrderContainer $orderContainer,
        MerchantDebtorEntity $merchantDebtor,
        IdentifiedDebtorCompany $debtorCompany,
        DebtorSettingsEntity $debtorSettings
    ) {
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);
        $orderContainer->getIdentifiedDebtorCompany()->willReturn($debtorCompany);
        $orderContainer->getDebtorSettings()->willReturn($debtorSettings);
    }

    public function it_returns_true_if_is_trusted_source(
        OrderContainer $orderContainer,
        IdentifiedDebtorCompany $debtorCompany
    ) {
        $debtorCompany->isTrustedSource()->willReturn(true);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(true, DebtorIsTrustedCheck::NAME));
    }

    public function it_returns_false_if_debtor_is_not_whitelisted(
        OrderContainer $orderContainer,
        DebtorSettingsEntity $debtorSettings,
        IdentifiedDebtorCompany $debtorCompany
    ) {
        $debtorCompany->isTrustedSource()->willReturn(false);
        $debtorSettings->isWhitelisted()->willReturn(false);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(false, DebtorIsTrustedCheck::NAME));
    }

    public function it_returns_true_if_debtor_is_whitelisted(
        OrderContainer $orderContainer,
        DebtorSettingsEntity $debtorSettings,
        IdentifiedDebtorCompany $debtorCompany
    ) {
        $debtorCompany->isTrustedSource()->willReturn(false);
        $debtorSettings->isWhitelisted()->willReturn(true);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(true, DebtorIsTrustedCheck::NAME));
    }
}
