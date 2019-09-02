<?php

namespace spec\App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\DebtorLimitManagerInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class MerchantDebtorLimitsServiceSpec extends ObjectBehavior
{
    private const MERCHANT_DEBTOR_ID = 10;

    private const DEBTOR_UUID = 'debtor_uuid';

    public function let(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        DebtorLimitManagerInterface $debtorLimitManager,
        MerchantDebtorEntity $merchantDebtor,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        MerchantSettingsEntity $merchantSettings,
        DebtorCompany $debtorCompany,
        OrderFinancialDetailsEntity $orderFinancialDetails,
        OrderContainer $orderContainer,
        LoggerInterface $logger
    ) {
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);
        $orderContainer->getMerchantSettings()->willReturn($merchantSettings);
        $orderContainer->getMerchantDebtorFinancialDetails()->willReturn($merchantDebtorFinancialDetails);
        $orderContainer->getDebtorCompany()->willReturn($debtorCompany);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $debtorCompany->getUuid()->willReturn(self::DEBTOR_UUID);

        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MerchantDebtorLimitsService::class);
    }

    public function it_is_enough(
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        DebtorCompany $debtorCompany,
        OrderFinancialDetailsEntity $orderFinancialDetails,
        OrderContainer $orderContainer
    ) {
        $testCases = [
            // order amount, merchant debtor power, debtor power, expected
            [0, 0, 0, true],
            [98, 99, 99, true],
            [100, 100, 100, true],
            [101, 101, 100, false],
            [101, 100, 101, false],
        ];

        foreach ($testCases as $testCase) {
            $merchantDebtorFinancialDetails->getFinancingPower()->willReturn($testCase[1]);
            $debtorCompany->getFinancingPower()->willReturn($testCase[2]);
            $orderFinancialDetails->getAmountGross()->willReturn($testCase[0]);
            $this->isEnough($orderContainer)->shouldBe($testCase[3]);
        }
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

        $merchantDebtorFinancialDetails->getFinancingLimit()->willReturn(10000.00);
        $merchantDebtorFinancialDetails->getFinancingPower()->willReturn(8000.00);

        $merchantDebtorFinancialDetails->setFinancingLimit(Argument::any())->shouldBeCalled()->willReturn($merchantDebtorFinancialDetails);
        $merchantDebtorFinancialDetails->setFinancingPower(Argument::any())->shouldBeCalled()->willReturn($merchantDebtorFinancialDetails);

        $this->recalculate($orderContainer);
    }

    public function it_locks_limit(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        DebtorLimitManagerInterface $debtorLimitManager,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        OrderFinancialDetailsEntity $orderFinancialDetails,
        OrderContainer $orderContainer
    ) {
        $orderFinancialDetails->getAmountGross()->willReturn(500);
        $debtorLimitManager->lockDebtorLimit(self::DEBTOR_UUID, 500)->shouldBeCalledOnce();
        $merchantDebtorFinancialDetails->reduceFinancingPower(500)->shouldBeCalledOnce();
        $merchantDebtorFinancialDetailsRepository->insert($merchantDebtorFinancialDetails)->shouldBeCalledOnce();

        $this->lock($orderContainer);
    }

    public function it_unlocks_limit(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        DebtorLimitManagerInterface $debtorLimitManager,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails,
        OrderFinancialDetailsEntity $orderFinancialDetails,
        OrderContainer $orderContainer
    ) {
        $orderFinancialDetails->getAmountGross()->willReturn(400);
        $debtorLimitManager->unlockDebtorLimit(self::DEBTOR_UUID, 400)->shouldBeCalledOnce();
        $merchantDebtorFinancialDetails->increaseFinancingPower(400)->shouldBeCalledOnce();
        $merchantDebtorFinancialDetailsRepository->insert($merchantDebtorFinancialDetails)->shouldBeCalledOnce();

        $this->unlock($orderContainer);
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

        $merchantDebtorFinancialDetails->getFinancingLimit()->willReturn(10000.00);
        $merchantDebtorFinancialDetails->getFinancingPower()->willReturn(8000.00);

        $merchantDebtorFinancialDetails->setFinancingLimit(Argument::any())->shouldNotBeCalled();
        $merchantDebtorFinancialDetails->setFinancingPower(Argument::any())->shouldNotBeCalled();

        $merchantDebtorFinancialDetailsRepository
            ->insert(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->recalculate($orderContainer);
    }
}
