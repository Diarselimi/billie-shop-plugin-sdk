<?php

namespace App\Application\UseCase\ConfirmPreApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderNotPreApprovedException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class ConfirmPreApprovedOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $orderRepository;

    private $stateManager;

    private $orderResponseFactory;

    private $orderPersistenceService;

    private $orderContainerFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $stateManager,
        OrderContainerFactory $orderContainerFactory,
        OrderPersistenceService $orderPersistenceService,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->stateManager = $stateManager;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderContainerFactory = $orderContainerFactory;
    }

    public function execute(ConfirmPreApprovedOrderRequest $request): OrderResponse
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid($request->getMerchantId(), $request->getOrderId());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException();
        }

        if (!$this->stateManager->isPreApproved($orderContainer->getOrder())) {
            throw new OrderWorkflowException();
        }

        $this->stateManager->approve($orderContainer);

        return $this->orderResponseFactory->create($orderContainer);
    }
}
