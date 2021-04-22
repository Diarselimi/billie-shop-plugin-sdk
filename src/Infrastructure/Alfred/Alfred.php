<?php

namespace App\Infrastructure\Alfred;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\DebtorCompanyFactory;
use App\DomainModel\DebtorCompany\DebtorCreationDTO;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorCompany\IdentifyDebtorResponseDTO;
use App\DomainModel\DebtorCompany\IdentifyDebtorResponseDTOFactory;
use App\DomainModel\ExternalDebtorResponse\ExternalDebtorFactory;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTO;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTOFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use App\DomainModel\SignatoryPower\SignatoryPowerAlreadySignedException;
use App\DomainModel\SignatoryPower\SignatoryPowerDTO;
use App\DomainModel\SignatoryPower\SignatoryPowerDTOFactory;
use App\DomainModel\SignatoryPower\SignatoryPowerSelectionDTO;
use App\DomainModel\TrackingAnalytics\DebtorEmailHashFactory;
use App\Infrastructure\Alfred\Dto\StrictMatchRequestDTO;
use App\Infrastructure\ClientResponseDecodeException;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

class Alfred implements CompaniesServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private const IDENTIFICATION_REQUEST_TIMEOUT = 15;

    private const EXTENDED_TIMEOUT = 2;

    private const SEARCH_EXTERNAL_DEBTORS_TIMEOUT = 3;

    private const HEADER_X_TRACKER_USER_ID = 'X-Tracker-User-Id';

    private $client;

    private $identifiedDebtorCompanyFactory;

    private $identifyDebtorResponseDTOFactory;

    private $signatoryPowersDTOFactory;

    private $externalDebtorFactory;

    private $identityVerificationCaseDTOFactory;

    public function __construct(
        Client $alfredClient,
        DebtorCompanyFactory $debtorCompanyFactory,
        SignatoryPowerDTOFactory $signatoryPowersDTOFactory,
        ExternalDebtorFactory $externalDebtorFactory,
        IdentityVerificationCaseDTOFactory $identityVerificationCaseDTOFactory,
        IdentifyDebtorResponseDTOFactory $identifyDebtorResponseDTOFactory
    ) {
        $this->client = $alfredClient;
        $this->identifiedDebtorCompanyFactory = $debtorCompanyFactory;
        $this->signatoryPowersDTOFactory = $signatoryPowersDTOFactory;
        $this->externalDebtorFactory = $externalDebtorFactory;
        $this->identityVerificationCaseDTOFactory = $identityVerificationCaseDTOFactory;
        $this->identifyDebtorResponseDTOFactory = $identifyDebtorResponseDTOFactory;
    }

    public function getDebtor(int $debtorCompanyId): ?DebtorCompany
    {
        return $this->doGetDebtor($debtorCompanyId);
    }

    /**
     * @param  array           $debtorIds
     * @return DebtorCompany[]
     */
    public function getDebtors(array $debtorIds): array
    {
        if (empty($debtorIds)) {
            return [];
        }

        try {
            $response = $this->client->get('debtors', [
                'query' => [
                    'ids' => $debtorIds,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'get_debtors');
                },
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException();
        }

        return $this->identifiedDebtorCompanyFactory->createFromMultipleDebtorCompaniesResponse($this->decodeResponse($response));
    }

    public function getDebtorByUuid(string $debtorCompanyUuid): ?DebtorCompany
    {
        return $this->doGetDebtor($debtorCompanyUuid);
    }

    public function getDebtorsByCrefoId(string $crefoId): array
    {
        try {
            $response = $this->client->get("debtor/crefo/{$crefoId}");

            return $this->identifiedDebtorCompanyFactory->createFromMultipleDebtorCompaniesResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return [];
            }

            throw new CompaniesServiceRequestException($exception);
        }
    }

    private function doGetDebtor($identifier): ?DebtorCompany
    {
        try {
            $response = $this->client->get("debtor/{$identifier}");

            return $this->identifiedDebtorCompanyFactory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function updateCompany(string $companyUuid, array $updateData): DebtorCompany
    {
        try {
            $response = $this->client->put("companies/{$companyUuid}", [
                'json' => $updateData,
                'timeout' => self::EXTENDED_TIMEOUT,
            ]);

            return $this->identifiedDebtorCompanyFactory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function updateCompanyBillingAddress(string $companyUuid, AddressEntity $addressEntity): UuidInterface
    {
        try {
            $response = $this->client->post(
                "companies/{$companyUuid}/billing-address",
                [
                    'json' => $addressEntity->toArray(),
                    'timeout' => self::EXTENDED_TIMEOUT,
                ]
            );

            $data = $this->decodeResponse($response);

            return Uuid::fromString($data['billing_address']['uuid']);
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function createDebtor(DebtorCreationDTO $debtorCreationDTO): DebtorCompany
    {
        try {
            $response = $this->client->post('debtors', [
                'json' => $debtorCreationDTO->toArray(),
                'timeout' => self::EXTENDED_TIMEOUT,
            ]);

            return $this->identifiedDebtorCompanyFactory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function synchronizeDebtor(int $debtorId): DebtorCompany
    {
        try {
            $response = $this->client->post(
                "debtor/$debtorId/synchronize"
            );

            return $this->identifiedDebtorCompanyFactory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): IdentifyDebtorResponseDTO
    {
        try {
            $response = $this->client->post('debtor/identify', [
                'json' => $requestDTO->toArray(),
                'headers' => [
                    self::HEADER_X_TRACKER_USER_ID => DebtorEmailHashFactory::create($requestDTO->getEmail()),
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'identify_debtor');
                },
                'timeout' => self::IDENTIFICATION_REQUEST_TIMEOUT,
            ]);

            $decodedResponse = $this->decodeResponse($response);

            return $this->identifyDebtorResponseDTOFactory->createFromCompaniesServiceResponse($decodedResponse);
        } catch (ClientException $exception) {
            if ($exception->getCode() !== Response::HTTP_NOT_FOUND) {
                throw new CompaniesServiceRequestException($exception);
            }

            $decodedResponse = $this->decodeResponse($exception->getResponse());

            return $this->identifyDebtorResponseDTOFactory->createFromCompaniesServiceResponse($decodedResponse, false);
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function identifyFirmenwissen(string $crefoId): DebtorCompany
    {
        try {
            $response = $this->client->post('debtor/identify/firmenwissen', [
                'json' => ['crefo_id' => $crefoId],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'identify_firmenwissen');
                },
                'timeout' => self::IDENTIFICATION_REQUEST_TIMEOUT,
            ]);

            $decodedResponse = $this->decodeResponse($response);

            return $this->identifiedDebtorCompanyFactory->createFromAlfredResponse($decodedResponse);
        } catch (ClientException | TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function strictMatchDebtor(StrictMatchRequestDTO $requestDTO): bool
    {
        try {
            $this->client->post('debtor/strict-match', [
                'json' => $requestDTO->toArray(),
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'strict_match_debtor');
                },
            ]);

            return true;
        } catch (ClientException $exception) {
            return false;
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function markDuplicates(MerchantDebtorDuplicateDTO ...$duplicates): void
    {
        $payload = ['duplicates' => []];

        foreach ($duplicates as $duplicate) {
            $payload['duplicates'][] = [
                'debtor_id' => $duplicate->getDebtorId(),
                'is_duplicate_of' => $duplicate->getParentDebtorId(),
            ];
        }

        try {
            $this->client->post('debtor/mark-duplicates', [
                'json' => $payload,
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    /**
     * @return SignatoryPowerDTO[]
     * @throws CompaniesServiceRequestException
     */
    public function getSignatoryPowers(string $companyIdentifier): array
    {
        try {
            $response = $this->client->get("debtor/{$companyIdentifier}/signatory-powers");
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }

        return $this->signatoryPowersDTOFactory->createFromArrayCollection($this->decodeResponse($response));
    }

    public function getSignatoryPowerDetails(string $token): ?SignatoryPowerDTO
    {
        try {
            $response = $this->client->get("signatory-powers/{$token}");
        } catch (ClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new CompaniesServiceRequestException($exception);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }

        return $this->signatoryPowersDTOFactory->createFromArray($this->decodeResponse($response));
    }

    public function acceptSignatoryPowerTc(string $signatoryPowerUuid): void
    {
        try {
            $this->client->post("signatory-powers/{$signatoryPowerUuid}/accept-tc");
        } catch (ClientException $exception) {
            if ($exception->getCode() === Response::HTTP_CONFLICT) {
                throw new SignatoryPowerAlreadySignedException();
            }
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function assignIdentityVerificationCase(string $caseUuid, string $signatoryPowerUuid): void
    {
        try {
            $this->client->post('signatory-powers/assign-identity-verification', [
                'json' => [
                    'signatory_power_uuid' => $signatoryPowerUuid,
                    'identity_verification_case_uuid' => $caseUuid,
                ],
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function saveSelectedSignatoryPowers(string $companyIdentifier, SignatoryPowerSelectionDTO ...$signatoryPowerDTOs)
    {
        $requestData['signatory_powers'] = array_map(function (SignatoryPowerSelectionDTO $signatoryPowerDTO) {
            return $signatoryPowerDTO->toArray();
        }, $signatoryPowerDTOs);

        try {
            $this->client->post("debtor/{$companyIdentifier}/signatory-powers-selection", [
                'json' => $requestData,
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function blacklistCompany(string $companyUuid): void
    {
        try {
            $this->client->post("company/{$companyUuid}/blacklist");
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function searchExternalDebtors(string $searchQuery, int $limit = 10): array
    {
        if (empty($searchQuery)) {
            return [];
        }

        try {
            $response = $this->client->post('company/search-customers-as-you-type', [
                'json' => [
                    'query' => $searchQuery,
                    'limit' => $limit,
                ],
                'timeout' => self::SEARCH_EXTERNAL_DEBTORS_TIMEOUT,
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException();
        }

        $decodedResponse = $this->decodeResponse($response);

        return $this->externalDebtorFactory->createFromArrayCollection(isset($decodedResponse['items']) ? $decodedResponse['items'] : []);
    }

    public function getIdentityVerificationCase(string $caseUuid): IdentityVerificationCaseDTO
    {
        try {
            $response = $this->client->get("identity-verification/{$caseUuid}");
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }

        $decodedResponse = $this->decodeResponse($response);

        return $this->identityVerificationCaseDTOFactory->createFromArray($decodedResponse);
    }
}
