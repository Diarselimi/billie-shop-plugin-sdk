<?php

namespace spec\App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MerchantDebtorLimitsServiceSpec extends ObjectBehavior
{
    private const MERCHANT_DEBTOR_ID = 10;

    public function let(
        CompaniesServiceInterface $companyService,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MerchantDebtorLimitsService::class);
    }

    public function it_recalculates_limit_on_order_complete(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorEntity $merchantDebtor,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        MerchantSettingsEntity $merchantSettings,
        OrderContainer $orderContainer
    ) {
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);

        $merchantSettings->getDebtorFinancingLimit()->willReturn(20000.00);

        $merchantDebtorFinancialDetails->getFinancingLimit()->willReturn(10000.00);
        $merchantDebtorFinancialDetails->getFinancingPower()->willReturn(8000.00);

        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);
        $orderContainer->getMerchantSettings()->willReturn($merchantSettings);
        $orderContainer->getMerchantDebtorFinancialDetails()->willReturn($merchantDebtorFinancialDetails);

        $orderRepository
            ->merchantDebtorHasOneCompleteOrder(self::MERCHANT_DEBTOR_ID)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $merchantDebtorFinancialDetails->setFinancingLimit(20000.00)->shouldBeCalled()->willReturn($merchantDebtorFinancialDetails);
        $merchantDebtorFinancialDetails->setFinancingPower(18000.00)->shouldBeCalled()->willReturn($merchantDebtorFinancialDetails);

        $merchantDebtorFinancialDetailsRepository
            ->insert($merchantDebtorFinancialDetails)
            ->shouldBeCalled()
        ;

        $this->recalculate($orderContainer);
    }

    public function it_doesnt_change_limit_if_the_current_one_is_higher_than_the_merchant_settings_one(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorEntity $merchantDebtor,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        MerchantSettingsEntity $merchantSettings,
        OrderContainer $orderContainer
    ) {
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);

        $merchantSettings->getDebtorFinancingLimit()->willReturn(5000.00);

        $merchantDebtorFinancialDetails->getFinancingLimit()->willReturn(10000.00);
        $merchantDebtorFinancialDetails->getFinancingPower()->willReturn(8000.00);

        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);
        $orderContainer->getMerchantSettings()->willReturn($merchantSettings);
        $orderContainer->getMerchantDebtorFinancialDetails()->willReturn($merchantDebtorFinancialDetails);

        $merchantDebtorFinancialDetails->setFinancingLimit(Argument::any())->shouldNotBeCalled();
        $merchantDebtorFinancialDetails->setFinancingPower(Argument::any())->shouldNotBeCalled();

        $merchantDebtorFinancialDetailsRepository
            ->insert(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->recalculate($orderContainer);
    }
}
