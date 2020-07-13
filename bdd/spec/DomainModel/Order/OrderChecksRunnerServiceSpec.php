<?php

namespace spec\App\DomainModel\Order;

use App\Application\UseCase\ApproveOrder\ApproveOrderUseCase;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\FraudScoreCheck;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntityFactory;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntity;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ServiceLocator;

class OrderChecksRunnerServiceSpec extends ObjectBehavior
{
    public function let(
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        OrderRiskCheckEntityFactory $riskCheckFactory,
        ServiceLocator $checkLoader,
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository
    ) {
        $this->beConstructedWith(
            $orderRiskCheckRepository,
            $riskCheckFactory,
            $checkLoader,
            $merchantRiskCheckSettingsRepository,
            [],
            []
        );
    }

    public function it_should_have_the_right_type()
    {
        $this->shouldHaveType(OrderChecksRunnerService::class);
    }

    public function it_should_not_run_skipped_risk_checks_on_rerun(
        OrderContainer $orderContainer,
        OrderRiskCheckEntity $orderRiskCheckEntity,
        RiskCheckDefinitionEntity $riskCheckDefinition,
        ServiceLocator $serviceLocator
    ) {
        $orderRiskCheckEntity->isPassed()->shouldBeCalledOnce()->willReturn(false);
        $riskCheckDefinition->getName()->willReturn(FraudScoreCheck::NAME);

        $orderRiskCheckEntity->getRiskCheckDefinition()->willReturn($riskCheckDefinition);

        $orderContainer->getRiskChecks()->willReturn([$orderRiskCheckEntity]);

        $this->rerunFailedChecks($orderContainer, ApproveOrderUseCase::RISK_CHECKS_TO_SKIP)->shouldReturn(true);
    }
}
