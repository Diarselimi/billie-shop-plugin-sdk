<?php

declare(strict_types=1);

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorScoring\DebtorScoringRequestDTO;
use App\DomainModel\DebtorScoring\DebtorScoringRequestDTOFactory;
use App\DomainModel\DebtorScoring\DebtorScoringResponseDTO;
use App\DomainModel\DebtorScoring\ScoringServiceInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\DebtorScoreAvailableCheck;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DebtorScoreAvailableCheckSpec extends ObjectBehavior
{
    use RiskCheckSpecHelperTrait;

    public function let(
        DebtorScoringRequestDTOFactory $scoringRequestFactory,
        ScoringServiceInterface $scoringService
    ) {
        $scoringRequestFactory->createFromOrderContainer(Argument::any())
            ->willReturn(new DebtorScoringRequestDTO());

        $this->beConstructedWith(...func_get_args());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DebtorScoreAvailableCheck::class);
    }

    public function it_will_return_true_and_avoid_scoring_if_the_debtor_is_not_trusted_source(
        OrderContainer $orderContainer
    ) {
        $this->setDebtorIsTrustedSourceFlag(false, $orderContainer);
        $this->setDebtorIsWhitelistedSourceFlag(false, $orderContainer);
        $this->check($orderContainer)->shouldBeLike($this->passCheckResult(DebtorScoreAvailableCheck::NAME));
    }

    public function it_will_return_true_and_avoid_scoring_if_the_debtor_is_whitelisted(
        OrderContainer $orderContainer
    ) {
        $this->setDebtorIsTrustedSourceFlag(true, $orderContainer);
        $this->setDebtorIsWhitelistedSourceFlag(true, $orderContainer);
        $this->check($orderContainer)->shouldBeLike($this->passCheckResult(DebtorScoreAvailableCheck::NAME));
    }

    public function it_should_not_score_again_if_order_container_has_scoring_response(
        ScoringServiceInterface $scoringService,
        OrderContainer $orderContainer
    ) {
        $this->setDebtorIsTrustedSourceFlag(true, $orderContainer);
        $this->setDebtorIsWhitelistedSourceFlag(false, $orderContainer);

        $response = (new DebtorScoringResponseDTO())->setHasFailed(false);
        $orderContainer->getDebtorScoringResponse()->willReturn($response);
        $scoringService->scoreDebtor(Argument::type(DebtorScoringRequestDTO::class))->shouldNotBeCalled();
        $this->check($orderContainer)->shouldBeLike($this->passCheckResult(DebtorScoreAvailableCheck::NAME));
    }

    public function it_should_request_score_if_order_container_has_no_scoring_response(
        ScoringServiceInterface $scoringService,
        OrderContainer $orderContainer
    ) {
        $this->setDebtorIsTrustedSourceFlag(true, $orderContainer);
        $this->setDebtorIsWhitelistedSourceFlag(false, $orderContainer);

        $orderContainer->getDebtorScoringResponse()->willReturn(null);
        $orderContainer->setDebtorScoringResponse(Argument::any())->willReturn($orderContainer);
        $response = (new DebtorScoringResponseDTO())->setHasFailed(false);
        $scoringService->scoreDebtor(Argument::type(DebtorScoringRequestDTO::class))
            ->shouldBeCalled()
            ->willReturn($response);
        $this->check($orderContainer)->shouldBeLike($this->passCheckResult(DebtorScoreAvailableCheck::NAME));
    }

    public function it_should_not_pass_if_score_is_failed(
        ScoringServiceInterface $scoringService,
        OrderContainer $orderContainer
    ) {
        $this->setDebtorIsTrustedSourceFlag(true, $orderContainer);
        $this->setDebtorIsWhitelistedSourceFlag(false, $orderContainer);

        $orderContainer->getDebtorScoringResponse()->willReturn(null);
        $orderContainer->setDebtorScoringResponse(Argument::any())->willReturn($orderContainer);
        $response = (new DebtorScoringResponseDTO())->setHasFailed(true);
        $scoringService->scoreDebtor(Argument::type(DebtorScoringRequestDTO::class))
            ->shouldBeCalled()->willReturn($response);
        $this->check($orderContainer)->shouldBeLike($this->notPassCheckResult(DebtorScoreAvailableCheck::NAME));
    }

    public function it_should_pass_if_score_is_not_failed(
        ScoringServiceInterface $scoringService,
        OrderContainer $orderContainer
    ) {
        $this->setDebtorIsTrustedSourceFlag(true, $orderContainer);
        $this->setDebtorIsWhitelistedSourceFlag(false, $orderContainer);

        $orderContainer->getDebtorScoringResponse()->willReturn(null);
        $orderContainer->setDebtorScoringResponse(Argument::any())->willReturn($orderContainer);
        $response = (new DebtorScoringResponseDTO())->setHasFailed(false);
        $scoringService->scoreDebtor(Argument::type(DebtorScoringRequestDTO::class))
            ->shouldBeCalled()->willReturn($response);
        $this->check($orderContainer)->shouldBeLike($this->passCheckResult(DebtorScoreAvailableCheck::NAME));
    }
}
