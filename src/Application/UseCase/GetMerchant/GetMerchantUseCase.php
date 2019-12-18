<?php

namespace App\Application\UseCase\GetMerchant;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class GetMerchantUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $merchantRepository;

    private $authenticationService;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        AuthenticationServiceInterface $authenticationService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->authenticationService = $authenticationService;
    }

    public function execute(GetMerchantRequest $request): GetMerchantResponse
    {
        $merchant = $request->getMerchantId() !== null ?
            $this->merchantRepository->getOneById($request->getMerchantId()) :
            $this->merchantRepository->getOneByUuid($request->getMerchantPaymentUuid());

        if (!$merchant) {
            throw new MerchantNotFoundException("Merchant with id or uuid {$request->getIdentifier()} not found");
        }

        $credentialsDto = null;

        try {
            $credentialsDto = $this->authenticationService->getCredentials($merchant->getOauthClientId());
        } catch (AuthenticationServiceRequestException $exception) {
            $this->logSuppressedException($exception, 'An error occurred calling the Oauth service.');
        }

        return new GetMerchantResponse($merchant, $credentialsDto);
    }
}
