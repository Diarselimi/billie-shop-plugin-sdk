<?php

declare(strict_types=1);

namespace spec\App\DomainModel\Order;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderRiskCheck\CheckResult;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntity;
use PhpSpec\ObjectBehavior;

class OrderDeclinedReasonsMapperSpec extends ObjectBehavior
{
    public function it_should_return_default_reason_on_not_mapped_risk_check(
        OrderEntity $entity,
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        OrderRiskCheckEntity $riskCheckEntity,
        RiskCheckDefinitionEntity $riskCheckDefinitionEntity,
        CheckResult $result
    ) {
        $result->getName()->willReturn('random_name');

        $this->mapReason($result)->shouldBe('risk_policy');
    }
}
