<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainerFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainer;

class GetMerchantDebtorUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $merchantDebtorContainerFactory;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorContainerFactory = $merchantDebtorContainerFactory;
    }

    public function execute(GetMerchantDebtorRequest $request): MerchantDebtorContainer
    {
        $this->validateRequest($request);

        $merchantDebtor = $request->getMerchantId() ?
            $this->merchantDebtorRepository->getOneByUuidAndMerchantId(
                $request->getMerchantDebtorUuid(),
                $request->getMerchantId()
            ) : $this->merchantDebtorRepository->getOneByUuid(
                $request->getMerchantDebtorUuid()
            );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        return $this->merchantDebtorContainerFactory->create($merchantDebtor);
    }
}
