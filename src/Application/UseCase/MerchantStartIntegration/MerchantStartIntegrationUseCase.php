<?php

namespace App\Application\UseCase\MerchantStartIntegration;

use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingContainerFactory;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\Sandbox\SandboxClientInterface;

class MerchantStartIntegrationUseCase
{
    private $onboardingContainerFactory;

    private $merchantRepository;

    private $sandboxClient;

    public function __construct(
        MerchantOnboardingContainerFactory $onboardingContainerFactory,
        MerchantRepository $merchantRepository,
        SandboxClientInterface $sandboxClient
    ) {
        $this->onboardingContainerFactory = $onboardingContainerFactory;
        $this->merchantRepository = $merchantRepository;
        $this->sandboxClient = $sandboxClient;
    }

    public function execute(MerchantStartIntegrationRequest $request): MerchantStartIntegrationResponse
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if (!$merchant->getSandboxPaymentUuid()) {
            throw new MerchantStartIntegrationNotAllowedException('Sandbox merchant does not exists');
        }

        $step = $this->onboardingContainerFactory->create($request->getMerchantId())
            ->getOnboardingStep(MerchantOnboardingStepEntity::STEP_TECHNICAL_INTEGRATION);

        if ($step->getState() !== MerchantOnboardingStepEntity::STATE_NEW) {
            throw new MerchantStartIntegrationNotAllowedException('Merchant Integration cannot be started: step is not in state new');
        }

        $sandboxCredentials = $this->sandboxClient->getMerchantCredentials($merchant->getSandboxPaymentUuid());

        return new MerchantStartIntegrationResponse(
            $sandboxCredentials->getClientId(),
            $sandboxCredentials->getSecret()
        );
    }
}
