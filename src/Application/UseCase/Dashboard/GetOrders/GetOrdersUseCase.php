<?php

namespace App\Application\UseCase\Dashboard\GetOrders;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\Aggregate\InvoiceLoader;
use App\DomainModel\Order\Search\OrderSearchQuery;
use App\DomainModel\Order\Search\OrderSearchRepositoryInterface;

class GetOrdersUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private OrderSearchRepositoryInterface $orderSearchRepository;

    private InvoiceLoader $invoiceLoader;

    public function __construct(
        OrderSearchRepositoryInterface $orderSearchRepository,
        InvoiceLoader $invoiceLoader
    ) {
        $this->orderSearchRepository = $orderSearchRepository;
        $this->invoiceLoader = $invoiceLoader;
    }

    public function execute(GetOrdersRequest $request): GetOrdersResponse
    {
        $this->validateRequest($request);

        $result = $this->orderSearchRepository->search(
            new OrderSearchQuery(
                $request->getOffset(),
                $request->getLimit(),
                $request->getSortBy(),
                $request->getSortDirection(),
                $request->getMerchantId(),
                $request->getSearchString(),
                $request->getFilters()
            )
        );

        $orderAggregateCollection = $this->invoiceLoader->load($result->getCollection());

        return new GetOrdersResponse($orderAggregateCollection, $result->getTotalCount());
    }
}
