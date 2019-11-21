<?php

namespace App\Application\UseCase\GetMerchantDebtors;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainerFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorList;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorListItem;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;

class GetMerchantDebtorsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $merchantDebtorContainerFactory;

    private $responseFactory;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory,
        MerchantDebtorResponseFactory $responseFactory
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorContainerFactory = $merchantDebtorContainerFactory;
        $this->responseFactory = $responseFactory;
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

        $merchantDebtors = array_map(function (array $row) use ($request) {
            return $this->createListItem($row['id']);
        }, $result['rows']);

        return $this->responseFactory->createList($result['total'], $merchantDebtors);
    }

    private function createListItem(int $id): MerchantDebtorListItem
    {
        $merchantDebtor = $this->merchantDebtorRepository->getOneById($id);

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $container = $this->merchantDebtorContainerFactory->create($merchantDebtor);

        return $this->responseFactory->createListItemFromContainer($container);
    }
}
