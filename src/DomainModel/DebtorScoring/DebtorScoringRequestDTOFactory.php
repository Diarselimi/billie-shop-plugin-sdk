<?php

namespace App\DomainModel\DebtorScoring;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationNotFoundException;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationService;

class DebtorScoringRequestDTOFactory
{
    private $scoreThresholdsConfigurationService;

    private $scoreThresholdsConfigurationRepository;

    private $orderRepository;

    public function __construct(
        ScoreThresholdsConfigurationService $scoreThresholdsConfigurationService,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        OrderRepository $orderRepository
    ) {
        $this->scoreThresholdsConfigurationService = $scoreThresholdsConfigurationService;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->orderRepository = $orderRepository;
    }

    public function createFromOrderContainer(OrderContainer $orderContainer): DebtorScoringRequestDTO
    {
        $debtorUuid = $orderContainer->getMerchantDebtor()->getCompanyUuid();
        $merchantSettings = $orderContainer->getMerchantSettings();
        $merchantDebtor = $orderContainer->getMerchantDebtor();

        $merchantScoreThresholds = $this->scoreThresholdsConfigurationRepository->getById(
            $merchantSettings->getScoreThresholdsConfigurationId()
        );

        if ($merchantScoreThresholds === null) {
            throw new ScoreThresholdsConfigurationNotFoundException();
        }

        $debtorScoreThresholds = $merchantDebtor->getScoreThresholdsConfigurationId()
            ? $this->scoreThresholdsConfigurationRepository
                ->getById($merchantDebtor->getScoreThresholdsConfigurationId())
            : null;

        return $this->create(
            $debtorUuid,
            $orderContainer->getDebtorExternalData()->isLegalFormSoleTrader(),
            $this->orderRepository->debtorHasAtLeastOneFullyPaidOrder($merchantDebtor->getCompanyUuid()),
            $merchantScoreThresholds,
            $debtorScoreThresholds
        );
    }

    public function create(
        string $debtorUuid,
        bool $isSoleTrader,
        bool $debtorHasAtLeastOneFullyPaidOrder,
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds,
        ?ScoreThresholdsConfigurationEntity $debtorScoreThresholds
    ): DebtorScoringRequestDTO {
        return (new DebtorScoringRequestDTO())
            ->setDebtorUuid($debtorUuid)
            ->setIsSoleTrader($isSoleTrader)
            ->setHasPaidInvoice($debtorHasAtLeastOneFullyPaidOrder)
            ->setCrefoLowScoreThreshold(
                $this->scoreThresholdsConfigurationService->getCrefoLowScoreThreshold(
                    $merchantScoreThresholds,
                    $debtorScoreThresholds
                )
            )
            ->setCrefoHighScoreThreshold(
                $this->scoreThresholdsConfigurationService->getCrefoHighScoreThreshold(
                    $merchantScoreThresholds,
                    $debtorScoreThresholds
                )
            )
            ->setSchufaLowScoreThreshold(
                $this->scoreThresholdsConfigurationService->getSchufaLowScoreThreshold(
                    $merchantScoreThresholds,
                    $debtorScoreThresholds
                )
            )
            ->setSchufaAverageScoreThreshold(
                $this->scoreThresholdsConfigurationService->getSchufaAverageScoreThreshold(
                    $merchantScoreThresholds,
                    $debtorScoreThresholds
                )
            )
            ->setSchufaHighScoreThreshold(
                $this->scoreThresholdsConfigurationService->getSchufaHighScoreThreshold(
                    $merchantScoreThresholds,
                    $debtorScoreThresholds
                )
            )
            ->setSchufaSoleTraderScoreThreshold(
                $this->scoreThresholdsConfigurationService->getSchufaSoleTraderScoreThreshold(
                    $merchantScoreThresholds,
                    $debtorScoreThresholds
                )
            );
    }
}
