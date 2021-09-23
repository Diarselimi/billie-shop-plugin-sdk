<?php

namespace App\Application\UseCase\UpdateDebtorWhitelist;

use App\Application\CommandHandler;
use App\Application\Exception\CompanyNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorSettings\DebtorSettingsEntityFactory;
use App\DomainModel\DebtorSettings\DebtorSettingsRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateDebtorWhitelistUseCase implements LoggingInterface, ValidatedUseCaseInterface, CommandHandler
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $debtorSettingsRepository;

    private $debtorSettingsEntityFactory;

    private $companiesService;

    public function __construct(
        DebtorSettingsRepositoryInterface $debtorSettingsRepository,
        DebtorSettingsEntityFactory $debtorSettingsEntityFactory,
        CompaniesServiceInterface $companiesService
    ) {
        $this->debtorSettingsRepository = $debtorSettingsRepository;
        $this->debtorSettingsEntityFactory = $debtorSettingsEntityFactory;
        $this->companiesService = $companiesService;
    }

    public function execute(UpdateDebtorWhitelistRequest $request): void
    {
        $this->validateRequest($request);

        $debtorSettings = $this->debtorSettingsRepository->getOneByCompanyUuid($request->getCompanyUuid());

        if ($debtorSettings) {
            $debtorSettings->setIsWhitelisted($request->isWhitelisted());
            $this->debtorSettingsRepository->update($debtorSettings);
            $this->logInfo('A debtor is_whitelisted flag is updated with status: ' . $debtorSettings->isWhitelisted());

            return;
        }

        try {
            $debtorCompany = $this->companiesService->getDebtorByUuid($request->getCompanyUuid());

            if (!$debtorCompany) {
                throw new CompanyNotFoundException();
            }
        } catch (CompaniesServiceRequestException $e) {
            throw new CompanyNotFoundException('Invalid uuid');
        }

        $debtorSettings = $this->debtorSettingsEntityFactory->create($request->getCompanyUuid(), $request->isWhitelisted());
        $this->debtorSettingsRepository->insert($debtorSettings);
        $this->logInfo(
            sprintf(
                'A debtor settings is created for companyUuid %s and is_whitelisted flag is set with status: %s',
                $request->getCompanyUuid(),
                $request->isWhitelisted()
            )
        );
    }
}
