<?php

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\CheckResult;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIsTrusted;
use PhpSpec\ObjectBehavior;

class DebtorIsTrustedSpec extends ObjectBehavior
{
    /**
     * @var OrderContainer
     */
    private $orderContainer;

    public function it_is_initializable()
    {
        $this->shouldHaveType(DebtorIsTrusted::class);
    }

    public function let()
    {
        $orderContainer = new OrderContainer();
        $merchantDebtor = new MerchantDebtorEntity();

        $merchantCompany = new DebtorCompany();
        $merchantCompany->setIsTrustedSource(false);
        $this->orderContainer = $orderContainer->setMerchantDebtor($merchantDebtor);
        $this->orderContainer->getMerchantDebtor()->setDebtorCompany($merchantCompany);
    }

    public function it_returns_true_if_is_trusted_source()
    {
        $orderContainer = $this->orderContainer;
        $orderContainer->getMerchantDebtor()->getDebtorCompany()->setIsTrustedSource(true);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(true, DebtorIsTrusted::NAME));
    }

    public function it_returns_false_if_debtor_is_not_whitelisted()
    {
        $this->orderContainer->getMerchantDebtor()->setIsWhitelisted(false);

        $this->check($this->orderContainer)->shouldBeLike(new CheckResult(false, DebtorIsTrusted::NAME));
    }

    public function it_should_return_check_result_with_true_value()
    {
        $this->orderContainer->getMerchantDebtor()->setIsWhiteListed(true);

        $this->check($this->orderContainer)->shouldBeLike(new CheckResult(true, DebtorIsTrusted::NAME));
    }
}
