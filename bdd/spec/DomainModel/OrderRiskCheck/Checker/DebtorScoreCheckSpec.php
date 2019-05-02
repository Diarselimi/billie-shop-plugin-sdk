<?php

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTO;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTOFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderRiskCheck\Checker\CheckResult;
use App\DomainModel\OrderRiskCheck\Checker\DebtorScoreCheck;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DebtorScoreCheckSpec extends ObjectBehavior
{
    /**
     * @var OrderContainer
     */
    private $orderContainer;

    private $companiesService;

    public function it_is_initializable()
    {
        $this->shouldHaveType(DebtorScoreCheck::class);
    }

    public function let(
        OrderRepositoryInterface $orderRepository,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        IsEligibleForPayAfterDeliveryRequestDTOFactory $afterDeliveryRequestDTOFactory,
        CompaniesServiceInterface $companiesService
    ) {
        $this->orderContainer = new OrderContainer();
        $this->orderContainer->setMerchantDebtor(new MerchantDebtorEntity());
        $this->orderContainer->setDebtorExternalData(new DebtorExternalDataEntity());
        $this->orderContainer->getMerchantDebtor()->setDebtorId(1);
        $this->orderContainer->setMerchantSettings(new MerchantSettingsEntity());
        $this->orderContainer->getMerchantSettings()->setScoreThresholdsConfigurationId(1);

        $score = new ScoreThresholdsConfigurationEntity();
        $score->setCrefoHighScoreThreshold(100);
        $score->setCrefoLowScoreThreshold(20);
        $score->setSchufaAverageScoreThreshold(50);
        $score->setSchufaLowScoreThreshold(20);
        $score->setSchufaHighScoreThreshold(100);
        $score->setSchufaSoleTraderScoreThreshold(99);

        $isEligible = new IsEligibleForPayAfterDeliveryRequestDTO();
        $isEligible->setCrefoHighScoreThreshold(100);
        $isEligible->setCrefoLowScoreThreshold(20);
        $isEligible->setSchufaAverageScoreThreshold(50);
        $isEligible->setSchufaLowScoreThreshold(20);
        $isEligible->setSchufaHighScoreThreshold(100);
        $isEligible->setSchufaSoleTraderScoreThreshold(99);

        $this->orderContainer->getMerchantDebtor()->setIsWhitelisted(false);

        $scoreThresholdsConfigurationRepository->getById(Argument::any())->willReturn($score);
        $orderRepository->debtorHasAtLeastOneFullyPaidOrder(Argument::any())->willReturn(true);

        $this->companiesService = $companiesService;

        $afterDeliveryRequestDTOFactory->create(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn($isEligible);

        $this->beConstructedWith(
            $orderRepository,
            $scoreThresholdsConfigurationRepository,
            $afterDeliveryRequestDTOFactory,
            $companiesService
            );
    }

    public function it_returns_false_check_result_if_companies_service_returns_false()
    {
        $this->companiesService->isEligibleForPayAfterDelivery(Argument::any())->willReturn(false);

        $this->check($this->orderContainer)
            ->shouldBeLike(new CheckResult(false, DebtorScoreCheck::NAME));
    }

    public function it_returns_false_check_result_if_companies_service_returns_true()
    {
        $this->companiesService->isEligibleForPayAfterDelivery(Argument::any())->willReturn(true);
        $this->check($this->orderContainer)
            ->shouldBeLike(new CheckResult(true, DebtorScoreCheck::NAME));
    }

    public function it_will_return_true_if_the_debtor_is_whitelisted()
    {
        $this->orderContainer->getMerchantDebtor()->setIsWhitelisted(true);
        $this->companiesService->isEligibleForPayAfterDelivery(Argument::any())->willReturn(false);

        $this->check($this->orderContainer)
            ->shouldBeLike(new CheckResult(true, DebtorScoreCheck::NAME));
    }
}
