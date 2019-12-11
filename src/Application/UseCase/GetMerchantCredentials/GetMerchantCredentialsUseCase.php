<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantCredentials;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;

class GetMerchantCredentialsUseCase
{
    private $merchantOnboardingRepository;

    private $authenticationService;

    public function __construct(
        MerchantOnboardingRepositoryInterface $merchantOnboardingRepository,
        AuthenticationServiceInterface $authenticationService
    ) {
        $this->merchantOnboardingRepository = $merchantOnboardingRepository;
        $this->authenticationService = $authenticationService;
    }

    public function execute(GetMerchantCredentialsRequest $request): GetMerchantCredentialsResponse
    {
        $merchantOnboarding = $this->merchantOnboardingRepository->findNewestByMerchant($request->getMerchantId());
        $response = new GetMerchantCredentialsResponse();

        if (!$merchantOnboarding || $merchantOnboarding->getState() !== MerchantOnboardingEntity::STATE_COMPLETE) {
            return $response;
        }

        $credentialsDTO = $this->authenticationService->getCredentials($request->getClientPublicId());

        return $response->setProduction($credentialsDTO->getClientId(), $credentialsDTO->getSecret());
    }
}
