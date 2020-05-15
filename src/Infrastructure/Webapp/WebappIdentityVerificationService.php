<?php

declare(strict_types=1);

namespace App\Infrastructure\Webapp;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceException;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceInterface;
use App\DomainModel\IdentityVerification\IdentityVerificationStartRequestDTO;
use App\DomainModel\IdentityVerification\IdentityVerificationStartResponseDTO;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

class WebappIdentityVerificationService implements IdentityVerificationServiceInterface, LoggingInterface
{
    use DecodeResponseTrait, LoggingTrait;

    private $client;

    private $merchantUserRepository;

    private $companiesService;

    public function __construct(
        Client $webappClient,
        MerchantUserRepositoryInterface $merchantUserRepository,
        CompaniesServiceInterface $companiesService
    ) {
        $this->client = $webappClient;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->companiesService = $companiesService;
    }

    public function startVerificationCase(IdentityVerificationStartRequestDTO $requestDTO): IdentityVerificationStartResponseDTO
    {
        try {
            $response = $this->client->post('sdk/identity-verification.json', [
                'json' => [
                    'first_name' => $requestDTO->getFirstName(),
                    'last_name' => $requestDTO->getLastName(),
                    'email' => $requestDTO->getEmail(),
                    'redirect_url_coupon_requested' => $requestDTO->getRedirectUrlCouponRequested(),
                    'redirect_url_review_pending' => $requestDTO->getRedirectUrlReviewPending(),
                    'redirect_url_declined' => $requestDTO->getRedirectUrlDeclined(),
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'webapp_identity_verification_start');
                },
            ]);

            $decodedResponse = $this->decodeResponse($response)['data'] ?? [];

            if (!isset($decodedResponse['uuid']) || !isset($decodedResponse['url'])) {
                throw new IdentityVerificationServiceException(null, 'Service responded with an unexpected response body.');
            }

            $response = (new IdentityVerificationStartResponseDTO())
                ->setUuid($decodedResponse['uuid'])
                ->setUrl($decodedResponse['url']);

            $this->assignVerificationCase($response->getUuid(), $requestDTO);
            $this->acceptTc($requestDTO);

            return $response;
        } catch (TransferException $exception) {
            throw new IdentityVerificationServiceException($exception);
        }
    }

    private function assignVerificationCase(string $caseUuid, IdentityVerificationStartRequestDTO $requestDTO)
    {
        if ($requestDTO->getMerchantUserId()) {
            $this->merchantUserRepository->assignIdentityVerificationCaseToUser($requestDTO->getMerchantUserId(), $caseUuid);
        }

        if ($requestDTO->getSignatoryPowerUuid()) {
            $this->companiesService->assignIdentityVerificationCase($caseUuid, $requestDTO->getSignatoryPowerUuid());
        }
    }

    private function acceptTc(IdentityVerificationStartRequestDTO $requestDTO)
    {
        if (!$requestDTO->getSignatoryPowerUuid()) {
            return;
        }

        try {
            $this->companiesService->acceptSignatoryPowerTc($requestDTO->getSignatoryPowerUuid());
        } catch (CompaniesServiceRequestException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 400) {
                // If TC already accepted, is ok, ignore it.
                return;
            }

            throw $e;
        }
    }
}
