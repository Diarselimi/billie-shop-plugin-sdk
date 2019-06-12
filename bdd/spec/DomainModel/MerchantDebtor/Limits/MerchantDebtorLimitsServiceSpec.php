<?php

namespace spec\App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class MerchantDebtorLimitsServiceSpec extends ObjectBehavior
{
    private const MERCHANT_DEBTOR_ID = 10;

    public function let(
        CompaniesServiceInterface $companyService,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorEntity $merchantDebtor,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        MerchantSettingsEntity $merchantSettings,
        OrderContainer $orderContainer,
        LoggerInterface $logger
    ) {
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);
        $orderContainer->getMerchantSettings()->willReturn($merchantSettings);
        $orderContainer->getMerchantDebtorFinancialDetails()->willReturn($merchantDebtorFinancialDetails);

        $merchantDebtorFinancialDetails->getFinancingLimit()->willReturn(10000.00);
        $merchantDebtorFinancialDetails->getFinancingPower()->willReturn(8000.00);

        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MerchantDebtorLimitsService::class);
    }

    public function it_recalculates_limit(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorEntity $merchantDebtor,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        MerchantSettingsEntity $merchantSettings,
        OrderContainer $orderContainer
    ) {
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);

        $merchantSettings->getDebtorFinancingLimit()->willReturn(20000.00);

        $orderRepository
            ->getOrdersCountByMerchantDebtorAndState(self::MERCHANT_DEBTOR_ID, 'complete')
            ->shouldBeCalled()
            ->willReturn(1)
        ;

        $merchantDebtorFinancialDetailsRepository
            ->insert($merchantDebtorFinancialDetails)
            ->shouldBeCalled()
        ;

        $merchantDebtorFinancialDetails->setFinancingLimit(Argument::any())->shouldBeCalled()->willReturn($merchantDebtorFinancialDetails);
        $merchantDebtorFinancialDetails->setFinancingPower(Argument::any())->shouldBeCalled()->willReturn($merchantDebtorFinancialDetails);

        $this->recalculate($orderContainer);
    }

    public function it_doesnt_change_limit_if_the_current_one_is_higher_than_the_merchant_settings_one(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        MerchantDebtorEntity $merchantDebtor,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        MerchantSettingsEntity $merchantSettings,
        OrderContainer $orderContainer
    ) {
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);

        $merchantSettings->getDebtorFinancingLimit()->willReturn(5000.00);

        $merchantDebtorFinancialDetails->setFinancingLimit(Argument::any())->shouldNotBeCalled();
        $merchantDebtorFinancialDetails->setFinancingPower(Argument::any())->shouldNotBeCalled();

        $merchantDebtorFinancialDetailsRepository
            ->insert(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->recalculate($orderContainer);
    }
}
