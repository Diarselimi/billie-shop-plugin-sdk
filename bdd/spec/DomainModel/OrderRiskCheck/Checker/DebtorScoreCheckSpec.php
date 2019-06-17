<?php

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTO;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTOFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderRiskCheck\Checker\CheckResult;
use App\DomainModel\OrderRiskCheck\Checker\DebtorScoreCheck;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DebtorScoreCheckSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(DebtorScoreCheck::class);
    }

    public function let(
        OrderRepositoryInterface $orderRepository,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        IsEligibleForPayAfterDeliveryRequestDTOFactory $afterDeliveryRequestDTOFactory,
        CompaniesServiceInterface $companiesService,
        OrderContainer $orderContainer,
        MerchantDebtorEntity $merchantDebtor,
        DebtorExternalDataEntity $debtorExternalData,
        MerchantSettingsEntity $merchantSettings,
        ScoreThresholdsConfigurationEntity $scoreThresholds,
        IsEligibleForPayAfterDeliveryRequestDTO $request
    ) {
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);
        $orderContainer->getDebtorExternalData()->willReturn($debtorExternalData);
        $orderContainer->getMerchantSettings()->willReturn($merchantSettings);

        $scoreThresholds->getCrefoHighScoreThreshold()->willReturn(100);
        $scoreThresholds->getCrefoLowScoreThreshold()->willReturn(20);
        $scoreThresholds->getSchufaAverageScoreThreshold()->willReturn(50);
        $scoreThresholds->getSchufaLowScoreThreshold()->willReturn(20);
        $scoreThresholds->getSchufaHighScoreThreshold()->willReturn(100);
        $scoreThresholds->getSchufaSoleTraderScoreThreshold()->willReturn(99);

        $request->getCrefoHighScoreThreshold()->willReturn(100);
        $request->getCrefoLowScoreThreshold()->willReturn(20);
        $request->getSchufaAverageScoreThreshold()->willReturn(50);
        $request->getSchufaLowScoreThreshold()->willReturn(20);
        $request->getSchufaHighScoreThreshold()->willReturn(100);
        $request->getSchufaSoleTraderScoreThreshold()->willReturn(99);

        $merchantDebtor->getDebtorId()->willReturn(1);
        $merchantDebtor->isWhitelisted()->willReturn(false);
        $merchantDebtor->getScoreThresholdsConfigurationId()->willReturn(1);

        $merchantSettings->getScoreThresholdsConfigurationId()->willReturn(1);
        $debtorExternalData->isLegalFormSoleTrader()->willReturn(false);

        $scoreThresholdsConfigurationRepository->getById(Argument::any())->willReturn($scoreThresholds);
        $orderRepository->debtorHasAtLeastOneFullyPaidOrder(Argument::any())->willReturn(true);

        $afterDeliveryRequestDTOFactory->create(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn($request);

        $this->beConstructedWith(...func_get_args());
    }

    public function it_returns_false_check_result_if_companies_service_returns_false(
        CompaniesServiceInterface $companiesService,
        OrderContainer $orderContainer
    ) {
        $companiesService->isEligibleForPayAfterDelivery(Argument::any())->willReturn(false);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(false, DebtorScoreCheck::NAME));
    }

    public function it_returns_false_check_result_if_companies_service_returns_true(
        CompaniesServiceInterface $companiesService,
        OrderContainer $orderContainer
    ) {
        $companiesService->isEligibleForPayAfterDelivery(Argument::any())->willReturn(true);
        $this->check($orderContainer)->shouldBeLike(new CheckResult(true, DebtorScoreCheck::NAME));
    }

    public function it_will_return_true_if_the_debtor_is_whitelisted(
        CompaniesServiceInterface $companiesService,
        MerchantDebtorEntity $merchantDebtor,
        OrderContainer $orderContainer
    ) {
        $merchantDebtor->isWhitelisted()->willReturn(true);
        $companiesService->isEligibleForPayAfterDelivery(Argument::any())->willReturn(false);

        $this->check($orderContainer)->shouldBeLike(new CheckResult(true, DebtorScoreCheck::NAME));
    }
}
