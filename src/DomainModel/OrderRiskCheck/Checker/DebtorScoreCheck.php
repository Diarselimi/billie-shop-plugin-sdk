<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorScoring\DebtorScoringRequestDTOFactory;
use App\DomainModel\DebtorScoring\ScoringServiceInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;

class DebtorScoreCheck implements CheckInterface
{
    const NAME = 'company_b2b_score';

    private $orderRepository;

    private $scoreThresholdsConfigurationRepository;

    private $debtorScoringRequestDTOFactory;

    private $scoringService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        DebtorScoringRequestDTOFactory $debtorScoringRequestDTOFactory,
        ScoringServiceInterface $scoringService
    ) {
        $this->orderRepository = $orderRepository;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->debtorScoringRequestDTOFactory = $debtorScoringRequestDTOFactory;
        $this->scoringService = $scoringService;
    }

    public function check(OrderContainer $orderContainer): CheckResult
    {
        $debtorUuid = $orderContainer->getMerchantDebtor()->getCompanyUuid();
        $merchantSettings = $orderContainer->getMerchantSettings();
        $merchantDebtor = $orderContainer->getMerchantDebtor();

        // If debtor is not from trusted source, we can't do scoring
        if (!$orderContainer->getDebtorCompany()->isTrustedSource() || $merchantDebtor->isWhitelisted()) {
            return new CheckResult(true, self::NAME);
        }

        $merchantScoreThresholds = $merchantSettings->getScoreThresholdsConfigurationId()
            ? $this->scoreThresholdsConfigurationRepository->getById($merchantSettings->getScoreThresholdsConfigurationId())
            : null
        ;

        $debtorScoreThresholds = $merchantDebtor->getScoreThresholdsConfigurationId()
            ? $this->scoreThresholdsConfigurationRepository->getById($merchantDebtor->getScoreThresholdsConfigurationId())
            : null
        ;

        $debtorScoringRequestDTO = $this->debtorScoringRequestDTOFactory->create(
            $debtorUuid,
            // TODO: refactor to pass the legalForm to this call, so alfred will decide if is sole trader or not. then remove DebtorExternalData\DebtorExternalDataEntity::LEGAL_FORMS_FOR_SOLE_TRADERS
            $orderContainer->getDebtorExternalData()->isLegalFormSoleTrader(),
            $this->orderRepository->debtorHasAtLeastOneFullyPaidOrder($merchantDebtor->getCompanyUuid()),
            $merchantScoreThresholds,
            $debtorScoreThresholds
        );

        $passed = $this->scoringService->isEligibleForPayAfterDelivery($debtorScoringRequestDTO);

        return new CheckResult($passed, self::NAME);
    }
}
