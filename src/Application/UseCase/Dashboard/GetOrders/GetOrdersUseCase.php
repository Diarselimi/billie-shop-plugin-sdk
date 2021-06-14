<?php

namespace App\Application\UseCase\Dashboard\GetOrders;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderCollectionRelationshipLoader;
use App\DomainModel\Order\Search\OrderSearchQuery;
use App\DomainModel\Order\Search\OrderSearchRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;

class GetOrdersUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private OrderSearchRepositoryInterface $orderSearchRepository;

    private OrderFinancialDetailsRepositoryInterface $financialDetailsRepository;

    private OrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private InvoiceServiceInterface $invoiceService;

    private OrderCollectionRelationshipLoader $orderCollectionRelationshipLoader;

    public function __construct(
        OrderSearchRepositoryInterface $orderSearchRepository,
        OrderCollectionRelationshipLoader $orderCollectionRelationshipLoader
    ) {
        $this->orderSearchRepository = $orderSearchRepository;
        $this->orderCollectionRelationshipLoader = $orderCollectionRelationshipLoader;
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

        $orderCollection = $result->getOrders();

        $this->orderCollectionRelationshipLoader->loadLatestFinancialDetails($orderCollection);
        $this->orderCollectionRelationshipLoader->loadOrderInvoicesWithInvoice($orderCollection);

        return new GetOrdersResponse($orderCollection, $result->getTotalCount());
    }
}
