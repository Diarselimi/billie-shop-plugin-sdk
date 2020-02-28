<?php

namespace App\Infrastructure\Alfred;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\DebtorCompanyFactory;
use App\DomainModel\DebtorCompany\DebtorCreationDTO;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use App\DomainModel\SignatoryPower\SignatoryPowerDTO;
use App\DomainModel\SignatoryPower\SignatoryPowerDTOFactory;
use App\DomainModel\SignatoryPower\SignatoryPowerSelectionDTO;
use App\Infrastructure\ClientResponseDecodeException;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Symfony\Component\HttpFoundation\Response;

class Alfred implements CompaniesServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private const IDENTIFICATION_REQUEST_TIMEOUT = 15;

    private $client;

    private $factory;

    private $signatoryPowersDTOFactory;

    public function __construct(
        Client $alfredClient,
        DebtorCompanyFactory $debtorFactory,
        SignatoryPowerDTOFactory $signatoryPowersDTOFactory
    ) {
        $this->client = $alfredClient;
        $this->factory = $debtorFactory;
        $this->signatoryPowersDTOFactory = $signatoryPowersDTOFactory;
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
        try {
            $response = $this->client->get("/debtors", [
                'query' => [
                    'ids' => $debtorIds,
                ],
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException();
        }

        return $this->factory->createFromMultipleDebtorCompaniesResponse($this->decodeResponse($response));
    }

    public function getDebtorByUuid(string $debtorCompanyUuid): ?DebtorCompany
    {
        return $this->doGetDebtor($debtorCompanyUuid);
    }

    public function getDebtorsByCrefoId(string $crefoId): array
    {
        try {
            $response = $this->client->get("/debtor/crefo/{$crefoId}");

            return $this->factory->createFromMultipleDebtorCompaniesResponse($this->decodeResponse($response));
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
            $response = $this->client->get("/debtor/{$identifier}");

            return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function updateDebtor(string $debtorUuid, array $updateData): DebtorCompany
    {
        try {
            $response = $this->client->put("/debtor/{$debtorUuid}", [
                'json' => $updateData,
            ]);

            return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function createDebtor(DebtorCreationDTO $debtorCreationDTO): DebtorCompany
    {
        try {
            $response = $this->client->post("/debtors", [
                'json' => $debtorCreationDTO->toArray(),
            ]);

            return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function synchronizeDebtor(int $debtorId): DebtorCompany
    {
        try {
            $response = $this->client->post(
                "/debtor/$debtorId/synchronize"
            );

            return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ?DebtorCompany
    {
        try {
            $response = $this->client->post("/debtor/identify", [
                'json' => $requestDTO->toArray(),
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'identify_debtor');
                },
                'timeout' => self::IDENTIFICATION_REQUEST_TIMEOUT,
            ]);

            $decodedResponse = $this->decodeResponse($response);

            return $this->factory->createFromAlfredResponse($decodedResponse);
        } catch (ClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $decodedResponse = $this->decodeResponse($exception->getResponse());

                if (isset($decodedResponse['suggestions']) && !empty($decodedResponse['suggestions'])) {
                    return $this->factory->createFromAlfredResponse(reset($decodedResponse['suggestions']), false);
                }

                return null;
            }

            throw new CompaniesServiceRequestException($exception);
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function strictMatchDebtor(string $debtorUuid, IdentifyDebtorRequestDTO $requestDTO): bool
    {
        try {
            $this->client->post("/debtor/strict-match", [
                'json' => array_merge(['expected_company_uuid' => $debtorUuid], $requestDTO->toArray()),
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
            $this->client->post("/debtor/mark-duplicates", [
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
            $response = $this->client->get("/debtor/{$companyIdentifier}/signatory-powers");
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }

        return $this->signatoryPowersDTOFactory->createFromArrayCollection($this->decodeResponse($response));
    }

    public function getSignatoryPowerDetails(string $token): ?SignatoryPowerDTO
    {
        try {
            $response = $this->client->get("/signatory-powers/{$token}");
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
            $this->client->post("/signatory-powers/{$signatoryPowerUuid}/accept-tc");
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function assignIdentityVerificationCase(string $caseUuid, string $signatoryPowerUuid): void
    {
        try {
            $this->client->post('/signatory-powers/assign-identity-verification', [
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
            $this->client->post("/debtor/{$companyIdentifier}/signatory-powers-selection", [
                'json' => $requestData,
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }
}
