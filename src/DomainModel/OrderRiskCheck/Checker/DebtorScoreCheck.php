<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTOFactory;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;

class DebtorScoreCheck implements CheckInterface
{
    const NAME = 'company_b2b_score';

    private $orderRepository;

    private $scoreThresholdsConfigurationRepository;

    private $eligibleForPayAfterDeliveryRequestDTOFactory;

    private $companiesService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        IsEligibleForPayAfterDeliveryRequestDTOFactory $eligibleForPayAfterDeliveryRequestDTOFactory,
        CompaniesServiceInterface $companiesService
    ) {
        $this->orderRepository = $orderRepository;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->eligibleForPayAfterDeliveryRequestDTOFactory = $eligibleForPayAfterDeliveryRequestDTOFactory;
        $this->companiesService = $companiesService;
    }

    public function check(OrderContainer $order): CheckResult
    {
        $debtorId = $order->getMerchantDebtor()->getDebtorId();
        $merchantSettings = $order->getMerchantSettings();
        $merchantDebtor = $order->getMerchantDebtor();

        $merchantScoreThresholds = ($merchantSettings->getScoreThresholdsConfigurationId()) ?
            $this->scoreThresholdsConfigurationRepository->getById($merchantSettings->getScoreThresholdsConfigurationId())
            : null;

        $debtorScoreThresholds = ($merchantDebtor->getScoreThresholdsConfigurationId()) ?
            $this->scoreThresholdsConfigurationRepository->getById($merchantDebtor->getScoreThresholdsConfigurationId())
            : null;

        $IsEligibleForPayAfterDeliveryRequestDTO = $this->eligibleForPayAfterDeliveryRequestDTOFactory->create(
            $debtorId,
            $order->getDebtorExternalData()->isLegalFormSoleTrader(),
            $this->orderRepository->debtorHasAtLeastOneFullyPaidOrder($debtorId),
            $merchantScoreThresholds,
            $debtorScoreThresholds
        );

        $passed = $this->companiesService->isEligibleForPayAfterDelivery($IsEligibleForPayAfterDeliveryRequestDTO);

        return new CheckResult($passed, self::NAME);
    }
}
