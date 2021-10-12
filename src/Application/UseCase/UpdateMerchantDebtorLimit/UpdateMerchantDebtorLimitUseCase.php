<?php

namespace App\Application\UseCase\UpdateMerchantDebtorLimit;

use App\Application\CommandHandler;
use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateMerchantDebtorLimitUseCase implements LoggingInterface, ValidatedUseCaseInterface, CommandHandler
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $merchantRepository;

    private $debtorLimitService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepository $merchantRepository,
        DebtorLimitServiceInterface $debtorLimitService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantRepository = $merchantRepository;
        $this->debtorLimitService = $debtorLimitService;
    }

    public function execute(UpdateMerchantDebtorLimitRequest $request): void
    {
        $this->validateRequest($request);

        $merchantDebtor = $this->merchantDebtorRepository->getOneByUuid($request->getMerchantDebtorUuid());

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $merchant = $this->merchantRepository->getOneById($merchantDebtor->getMerchantId());

        $this->debtorLimitService->update(
            $merchantDebtor->getCompanyUuid(),
            $merchant->getCompanyUuid(),
            $request->getLimit()
        );
    }
}
