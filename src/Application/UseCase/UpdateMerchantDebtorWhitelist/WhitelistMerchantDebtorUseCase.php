<?php

namespace App\Application\UseCase\UpdateMerchantDebtorWhitelist;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class WhitelistMerchantDebtorUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    public function __construct(MerchantDebtorRepositoryInterface $merchantDebtorRepository)
    {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
    }

    public function execute(WhitelistMerchantDebtorRequest $request): void
    {
        $merchantDebtor = $this->merchantDebtorRepository->getOneByExternalIdAndMerchantId(
            $request->getMerchantDebtorExternalId(),
            $request->getMerchantId(),
            []
        );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException("Merchant Debtor with external id #{$request->getMerchantDebtorExternalId()} not found");
        }

        $this->validateRequest($request);

        $merchantDebtor->setIsWhitelisted($request->getIsWhitelisted());

        $this->merchantDebtorRepository->update($merchantDebtor);

        $this->logInfo("A merchant debtor whitelisted is updated with status: " . $merchantDebtor->isWhitelisted());
    }
}
