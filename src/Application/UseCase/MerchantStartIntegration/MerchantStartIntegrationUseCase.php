<?php

namespace App\Application\UseCase\MerchantStartIntegration;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Merchant\MerchantWithCompanyCreationDTO;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingContainerFactory;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\Sandbox\SandboxClientInterface;
use App\DomainModel\Sandbox\SandboxServiceRequestException;

class MerchantStartIntegrationUseCase
{
    private const SANDBOX_MERCHANT_LIMIT = 1000000;

    private const SANDBOX_MERCHANT_DEBTOR_LIMIT = 10000;

    private $onboardingContainerFactory;

    private $merchantRepository;

    private $sandboxClient;

    private $companiesService;

    public function __construct(
        MerchantOnboardingContainerFactory $onboardingContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        SandboxClientInterface $sandboxClient,
        CompaniesServiceInterface $companiesService
    ) {
        $this->onboardingContainerFactory = $onboardingContainerFactory;
        $this->merchantRepository = $merchantRepository;
        $this->sandboxClient = $sandboxClient;
        $this->companiesService = $companiesService;
    }

    public function execute(MerchantStartIntegrationRequest $request): MerchantStartIntegrationResponse
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if ($merchant->getSandboxPaymentUuid()) {
            throw new MerchantStartIntegrationNotAllowedException('Merchant Integration cannot be started: sandbox merchant already exists');
        }

        $step = $this->onboardingContainerFactory->create($request->getMerchantId())
            ->getOnboardingStep(MerchantOnboardingStepEntity::STEP_TECHNICAL_INTEGRATION);

        if ($step->getState() !== MerchantOnboardingStepEntity::STATE_NEW) {
            throw new MerchantStartIntegrationNotAllowedException('Merchant Integration cannot be started: step is not in state new');
        }

        $company = $this->companiesService->getDebtor($merchant->getCompanyId());
        $creationDTO = (new MerchantWithCompanyCreationDTO())
            ->setIsOnboardingComplete(true)
            ->setMerchantFinancingLimit(self::SANDBOX_MERCHANT_LIMIT)
            ->setInitialDebtorFinancingLimit(self::SANDBOX_MERCHANT_DEBTOR_LIMIT)
            ->setName($merchant->getName())
            ->setSchufaId($company->getSchufaId())
            ->setCrefoId($company->getCrefoId())
            ->setAddressStreet($company->getAddressStreet())
            ->setAddressHouse($company->getAddressHouse())
            ->setAddressPostalCode($company->getAddressPostalCode())
            ->setAddressCity($company->getAddressCity())
            ->setAddressCountry($company->getAddressCountry())
            ->setLegalForm($company->getLegalForm())
            ;

        try {
            /** @var MerchantWithCompanyCreationDTO $creationDTO */
            $sandboxClientDTO = $this->sandboxClient->createMerchant($creationDTO);
        } catch (SandboxServiceRequestException $exception) {
            throw new MerchantStartIntegrationException($exception->getMessage(), null, $exception);
        }

        $merchant->setSandboxPaymentUuid($sandboxClientDTO->getMerchant()->getPaymentUuid());
        $this->merchantRepository->update($merchant);

        return new MerchantStartIntegrationResponse(
            $sandboxClientDTO->getMerchant()->getOauthClientId(),
            $sandboxClientDTO->getOauthClientSecret()
        );
    }
}
