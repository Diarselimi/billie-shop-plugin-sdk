<?php

namespace App\Application\UseCase\GetOrders;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\OrderResponseFactory;

class GetOrdersUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderRepository;

    private $orderPersistenceService;

    private $orderResponseFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(GetOrdersRequest $request): GetOrdersResponse
    {
        $this->validateRequest($request);

        $result = $this->orderRepository->getByMerchantId(
            $request->getMerchantId(),
            $request->getOffset(),
            $request->getLimit(),
            $request->getSortBy(),
            $request->getSortDirection(),
            $request->getSearchString(),
            $request->getFilters()
        );

        $orders = array_map(function (OrderEntity $orderEntity) {
            $orderContainer = $this->orderPersistenceService->createFromOrderEntity($orderEntity);

            return $this->orderResponseFactory->create($orderContainer);
        }, $result['orders']);

        return new GetOrdersResponse($result['total'], ...$orders);
    }
}
