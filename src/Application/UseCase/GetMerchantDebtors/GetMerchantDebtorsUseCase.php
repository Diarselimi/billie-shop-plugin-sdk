<?php

namespace App\Application\UseCase\GetMerchantDebtors;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainerFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorList;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;

class GetMerchantDebtorsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $merchantDebtorContainerFactory;

    private $responseFactory;

    private $entityFactory;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory,
        MerchantDebtorResponseFactory $responseFactory,
        MerchantDebtorEntityFactory $entityFactory
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorContainerFactory = $merchantDebtorContainerFactory;
        $this->responseFactory = $responseFactory;
        $this->entityFactory = $entityFactory;
    }

    public function execute(GetMerchantDebtorsRequest $request): MerchantDebtorList
    {
        $this->validateRequest($request);

        $result = $this->merchantDebtorRepository->getByMerchantId(
            $request->getMerchantId(),
            $request->getOffset(),
            $request->getLimit(),
            $request->getSortBy(),
            $request->getSortDirection(),
            $request->getSearchString()
        );

        $merchantDebtors = array_map(function (MerchantDebtorEntity $merchantDebtorEntity) {
            $container = $this->merchantDebtorContainerFactory->create($merchantDebtorEntity);

            return $this->responseFactory->createListItemFromContainer($container);
        }, $result['rows']);

        return $this->responseFactory->createList($result['total'], $merchantDebtors);
    }
}
