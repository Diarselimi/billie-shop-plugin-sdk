<?php

namespace App\Application\UseCase\UpdateMerchantDebtorCompany;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\Infrastructure\Alfred\AlfredRequestException;
use App\Infrastructure\Alfred\AlfredResponseDecodeException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateMerchantDebtorCompanyUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $companiesService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->companiesService = $companiesService;
    }

    public function execute(UpdateMerchantDebtorCompanyRequest $request): void
    {
        $this->validateRequest($request);

        $merchantDebtor = $this->merchantDebtorRepository->getOneByExternalIdAndMerchantId(
            $request->getMerchantDebtorExternalId(),
            $request->getMerchantId(),
            []
        );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $originalDebtor = $this->companiesService->getDebtor($merchantDebtor->getDebtorId());

        try {
            $updateData = $this->prepareUpdateData($request);
            $updatedDebtor = $this->companiesService->updateDebtor($merchantDebtor->getDebtorId(), $updateData);
        } catch (AlfredResponseDecodeException | AlfredRequestException $exception) {
            throw new MerchantDebtorUpdateFailedException();
        }

        $this->logUpdateDetails($request->getMerchantDebtorExternalId(), $merchantDebtor, $originalDebtor, $updatedDebtor);
    }

    private function logUpdateDetails(
        string $externalId,
        MerchantDebtorEntity $merchantDebtor,
        DebtorCompany $originalDebtor,
        DebtorCompany $updatedDebtor
    ) {
        $this->logInfo('Merchant debtor {external_id} (id:{id}) company data updated', [
            'external_id' => $externalId,
            'merchant_id' => $merchantDebtor->getMerchantId(),
            'id' => $merchantDebtor->getId(),
            'company_id' => $merchantDebtor->getDebtorId(),
            'old_name' => $originalDebtor->getName(),
            'new_name' => $updatedDebtor->getName(),
            'old_house' => $originalDebtor->getAddressHouse(),
            'new_house' => $updatedDebtor->getAddressHouse(),
            'old_street' => $originalDebtor->getAddressStreet(),
            'new_street' => $updatedDebtor->getAddressStreet(),
            'old_city' => $originalDebtor->getAddressCity(),
            'new_city' => $updatedDebtor->getAddressCity(),
            'old_postal_code' => $originalDebtor->getAddressPostalCode(),
            'new_postal_code' => $updatedDebtor->getAddressPostalCode(),
        ]);
    }

    private function prepareUpdateData(UpdateMerchantDebtorCompanyRequest $request): array
    {
        $updateData = [
            'name' => $request->getName(),
            'address_house' => $request->getAddressHouse(),
            'address_street' => $request->getAddressStreet(),
            'address_city' => $request->getAddressCity(),
            'address_postal_code' => $request->getAddressPostalCode(),
        ];

        return array_filter($updateData);
    }
}
