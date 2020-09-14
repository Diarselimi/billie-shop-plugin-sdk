<?php

namespace App\Application\UseCase\SynchronizeMerchantDebtor;

use App\Application\Exception\DebtorNotSynchronizedException;
use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorSynchronizationResponse;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class SynchronizeMerchantDebtorUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $companiesService;

    private $responseFactory;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorResponseFactory $responseFactory
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->companiesService = $companiesService;
        $this->responseFactory = $responseFactory;
    }

    public function execute(string $merchantDebtorUuid): MerchantDebtorSynchronizationResponse
    {
        $merchantDebtorEntity = $this->merchantDebtorRepository->getOneByUuid($merchantDebtorUuid);

        if (!$merchantDebtorEntity) {
            throw new MerchantDebtorNotFoundException();
        }

        try {
            $debtorCompany = $this->companiesService->synchronizeDebtor($merchantDebtorEntity->getDebtorId());
        } catch (CompaniesServiceRequestException $exception) {
            $this->logError("Request for synchronization failed", [
                LoggingInterface::KEY_UUID => $merchantDebtorUuid,
                LoggingInterface::KEY_SOBAKA => ['exception' => $exception],
            ]);

            throw new DebtorNotSynchronizedException();
        }

        return $this->responseFactory->createFromDebtorCompany($debtorCompany);
    }
}
