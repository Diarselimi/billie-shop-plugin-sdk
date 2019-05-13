<?php

namespace App\Application\UseCase\UpdateMerchantDebtorWhitelist;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorResponse;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorResponseFactory;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class WhitelistMerchantDebtorUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $merchantDebtorResponseFactory;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorResponseFactory $merchantDebtorResponseFactory
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorResponseFactory = $merchantDebtorResponseFactory;
    }

    public function execute(WhitelistMerchantDebtorRequest $request): GetMerchantDebtorResponse
    {
        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantExternalId(
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

        return $this->merchantDebtorResponseFactory->create(
            $merchantDebtor,
            $request->getMerchantDebtorExternalId()
        );
    }
}
