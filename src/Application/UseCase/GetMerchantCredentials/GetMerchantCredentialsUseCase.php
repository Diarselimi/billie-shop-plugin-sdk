<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantCredentials;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\Sandbox\SandboxClientInterface;
use App\DomainModel\Sandbox\SandboxServiceRequestException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class GetMerchantCredentialsUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $merchantOnboardingRepository;

    private $authenticationService;

    private $sandboxClient;

    public function __construct(
        MerchantOnboardingRepositoryInterface $merchantOnboardingRepository,
        AuthenticationServiceInterface $authenticationService,
        SandboxClientInterface $sandboxClient
    ) {
        $this->merchantOnboardingRepository = $merchantOnboardingRepository;
        $this->authenticationService = $authenticationService;
        $this->sandboxClient = $sandboxClient;
    }

    public function execute(GetMerchantCredentialsRequest $request): GetMerchantCredentialsResponse
    {
        $merchantOnboarding = $this->merchantOnboardingRepository->findNewestByMerchant($request->getMerchantId());
        $response = new GetMerchantCredentialsResponse();

        if ($merchantOnboarding->getState() === MerchantOnboardingEntity::STATE_COMPLETE) {
            $this->addProductionCredentialsToResponse($request, $response);
        }
        $this->addSandboxCredentialsToResponse($request, $response);

        return $response;
    }

    private function addProductionCredentialsToResponse(GetMerchantCredentialsRequest $request, GetMerchantCredentialsResponse $response): void
    {
        $prodCredentials = $this->authenticationService->getCredentials($request->getClientPublicId());
        if ($prodCredentials) {
            $response->setProduction($prodCredentials->getClientId(), $prodCredentials->getSecret());
        }
    }

    private function addSandboxCredentialsToResponse(GetMerchantCredentialsRequest $request, GetMerchantCredentialsResponse $response): void
    {
        try {
            if ($request->getSandboxMerchantPaymentUuid()) {
                $credentialsDTO = $this->sandboxClient->getMerchantCredentials($request->getSandboxMerchantPaymentUuid());
                if ($credentialsDTO->getClientId() !== null) {
                    $response->setSandbox($credentialsDTO->getClientId(), $credentialsDTO->getSecret());
                }
            }
        } catch (SandboxServiceRequestException $e) {
            $this->logSuppressedException($e, 'Failed to get sandbox credentials.');
        }
    }
}
