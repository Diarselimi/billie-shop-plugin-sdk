<?php

declare(strict_types=1);

namespace App\Infrastructure\SepaB2BGenerator;

use App\DomainModel\BankAccount\BankAccountDTO;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceResponseDTO;
use App\DomainModel\SepaB2BGenerator\DocumentGeneratorClientInterface;
use App\DomainModel\SepaB2BGenerator\SepaB2BDocumentGenerationRequestDTO;
use App\Infrastructure\FinTechToolbox\FinTechToolboxResponseDTO;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class SepaB2BGeneratorService implements LoggingInterface
{
    use LoggingTrait;

    private $client;

    private $companiesService;

    private $fileService;

    public function __construct(
        DocumentGeneratorClientInterface $client,
        CompaniesServiceInterface $companiesService,
        FileServiceInterface $fileService
    ) {
        $this->client = $client;
        $this->companiesService = $companiesService;
        $this->fileService = $fileService;
    }

    public function generate(string $companyUuid, BankAccountDTO $bankAccountDTO, FinTechToolboxResponseDTO $bankInfo, string $mandateReference): FileServiceResponseDTO
    {
        $debtorCompany = $this->companiesService->getDebtorByUuid($companyUuid);

        $b2bGeneratorDTO = (new SepaB2BDocumentGenerationRequestDTO())
            ->setBankAccountOwner($bankAccountDTO->getName())
            ->setBankIban($bankAccountDTO->getIban()->getIban())
            ->setBankMandateReference($mandateReference)
            ->setBankBic($bankAccountDTO->getBic())
            ->setBankName($bankInfo->getBankName())
            ->setAddressStreet($debtorCompany->getAddressStreet())
            ->setAddressCityName($debtorCompany->getAddressCity())
            ->setAddressPostcode($debtorCompany->getAddressPostalCode())
            ->setAddressHouseNumber($debtorCompany->getAddressHouse());

        $fileBase64 = $this->client->generate($b2bGeneratorDTO);

        return $this->fileService->upload(
            $fileBase64,
            md5($b2bGeneratorDTO->getBankIban()).'_'.time(),
            'sepa_b2b_mandate'
        );
    }
}
