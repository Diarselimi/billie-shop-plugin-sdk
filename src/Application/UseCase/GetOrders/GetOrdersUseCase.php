<?php

namespace App\Application\UseCase\GetOrders;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\OrderResponseFactory;

class GetOrdersUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderRepository;

    private $orderContainerFactory;

    private $orderResponseFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(GetOrdersRequest $request): GetOrdersResponse
    {
        $this->validateRequest($request);

        $result = $this->orderRepository->search(
            $request->getMerchantId(),
            $request->getOffset(),
            $request->getLimit(),
            $request->getSortBy(),
            $request->getSortDirection(),
            $request->getSearchString(),
            $request->getFilters()
        );

        $orderContainers = array_map(function (OrderEntity $orderEntity) {
            return $this->orderContainerFactory->createFromOrderEntity($orderEntity);
        }, $result['orders']);

        $orderResponses = $this->orderResponseFactory->createFromOrderContainers($orderContainers);

        return new GetOrdersResponse($result['total'], ...$orderResponses);
    }
}
