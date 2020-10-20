<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorScoring\DebtorScoringRequestDTOFactory;
use App\DomainModel\DebtorScoring\DebtorScoringResponseDTO;
use App\DomainModel\DebtorScoring\ScoringServiceInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;

abstract class AbstractDebtorScoreCheck implements CheckInterface
{
    private $scoringRequestFactory;

    private $scoringService;

    public function __construct(
        DebtorScoringRequestDTOFactory $debtorScoringRequestDTOFactory,
        ScoringServiceInterface $scoringService
    ) {
        $this->scoringRequestFactory = $debtorScoringRequestDTOFactory;
        $this->scoringService = $scoringService;
    }

    abstract protected function getName(): string;

    abstract protected function createCheckResult(DebtorScoringResponseDTO $scoringResponse): CheckResult;

    public function check(OrderContainer $orderContainer): CheckResult
    {
        if (!$this->shouldBeScored($orderContainer)) {
            return new CheckResult(true, $this->getName());
        }

        $scoringResponse = $orderContainer->getDebtorScoringResponse();

        if ($scoringResponse === null) {
            $scoringRequest = $this->scoringRequestFactory->createFromOrderContainer($orderContainer);
            $scoringResponse = $this->scoringService->scoreDebtor($scoringRequest);

            $orderContainer->setDebtorScoringResponse($scoringResponse);
        }

        return $this->createCheckResult($scoringResponse);
    }

    private function shouldBeScored(OrderContainer $orderContainer): bool
    {
        // If debtor is not from trusted source, we can't do scoring
        if (!$orderContainer->getIdentifiedDebtorCompany()->isTrustedSource()) {
            return false;
        }

        // If debtor is whitelisted there is no need to do scoring
        if ($orderContainer->getDebtorSettings()->isWhitelisted()) {
            return false;
        }

        return true;
    }
}
