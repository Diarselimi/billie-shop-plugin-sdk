<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantDebtors;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\SearchMerchantDebtorsDTOFactory;
use App\DomainModel\MerchantDebtor\SearchMerchantDebtorsRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainerFactory;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorList;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;

class GetMerchantDebtorsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $searchMerchantDebtorRepository;

    private $merchantDebtorContainerFactory;

    private $responseFactory;

    private $searchMerchantDebtorFactory;

    private $entityFactory;

    public function __construct(
        SearchMerchantDebtorsRepositoryInterface $searchMerchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory,
        MerchantDebtorResponseFactory $responseFactory,
        SearchMerchantDebtorsDTOFactory $searchMerchantDebtorFactory,
        MerchantDebtorEntityFactory $entityFactory
    ) {
        $this->searchMerchantDebtorRepository = $searchMerchantDebtorRepository;
        $this->merchantDebtorContainerFactory = $merchantDebtorContainerFactory;
        $this->responseFactory = $responseFactory;
        $this->searchMerchantDebtorFactory = $searchMerchantDebtorFactory;
        $this->entityFactory = $entityFactory;
    }

    public function execute(GetMerchantDebtorsRequest $request): MerchantDebtorList
    {
        $this->validateRequest($request);

        $result = $this->searchMerchantDebtorRepository->searchMerchantDebtors($this->searchMerchantDebtorFactory->create($request));

        $merchantDebtors = array_map(function (array $merchantDebtorEntity) {
            $container = $this->merchantDebtorContainerFactory->create(
                $this->entityFactory->createFromArray($merchantDebtorEntity)
            );

            return $this->responseFactory->createListItemFromContainer($container);
        }, $result->getItems());

        return $this->responseFactory->createList($result->getTotal(), $merchantDebtors);
    }
}
